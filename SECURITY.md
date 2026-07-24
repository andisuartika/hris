# Security Guidelines for HRIS API

## Overview
This document outlines the security measures implemented in the HRIS API and guidelines for maintaining security in production.

## Implemented Security Features

### 1. Authentication & Authorization
- **SSO Integration**: Keycloak OAuth2/OpenID Connect for centralized user management
- **Token-Based API Authentication**: Laravel Sanctum with 30-day expiration
- **Role-Based Access Control**: Spatie Permission for granular permissions
- **Password Grant Flow**: Mobile-optimized for client credentials validation

### 2. API Response Standardization
All API responses follow a standard JSON envelope format:
```json
{
  "success": boolean,
  "message": string,
  "data": any,
  "errors": object,
  "code": string
}
```

Exception handling returns consistent error formats for all HTTP status codes:
- 401: UNAUTHENTICATED - Missing or invalid authentication
- 403: FORBIDDEN - Insufficient permissions
- 404: NOT_FOUND - Resource not found
- 405: METHOD_NOT_ALLOWED - HTTP method not supported
- 422: VALIDATION_ERROR - Request validation failed
- 429: TOO_MANY_REQUESTS - Rate limit exceeded
- 500: SERVER_ERROR - Server-side error

### 3. Rate Limiting & Throttling
- **Global API Limit**: 60 requests/minute per user/IP for all `/api/*` routes
- **Login Throttle**: 5 login attempts/minute per IP to prevent brute force
- Implemented via middleware in `bootstrap/app.php`

### 4. Debug Mode
- **APP_DEBUG=false** in production
- Debug mode MUST be disabled to prevent stack trace exposure in error responses
- Check `config/app.php` for the `debug` setting

### 5. Face Recognition Verification
- Server-side cosine similarity calculation (0-100 scale)
- Configurable threshold per company (default: 85)
- Prevents unauthorized access via face spoofing

### 6. Location Verification
- Haversine distance calculation for geofence validation
- Configurable radius per office location
- Prevents remote attendance without physical presence

### 7. Audit Logging
- Attendance events logged to `attendance_logs` table
- Captured data: timestamp, coordinates, face similarity score, photo, verification status
- Enables compliance and fraud detection

## Environment Variables & Secrets

### Required Environment Variables
All variables in `.env.example` are required for production. **Never commit actual values.**

### Critical Variables
```env
# Database
DB_PASSWORD=              # MySQL password
DB_USERNAME=              # MySQL username

# Keycloak
KEYCLOAK_CLIENT_SECRET=   # OAuth2 client secret (rotate regularly)

# Application
APP_KEY=                  # Laravel encryption key (generate with: php artisan key:generate)
APP_DEBUG=false           # MUST be false in production
```

### Secret Management
1. **Generate secrets during setup**: Use `php artisan key:generate` for APP_KEY
2. **Never commit secrets**: Use `.gitignore` to exclude `.env` and `.env.docker`
3. **Rotate Keycloak secret**: After removing from git history, generate new client secret in Keycloak admin console
4. **Use secure storage**: For production, use:
   - Environment configuration management (AWS Secrets Manager, Hashicorp Vault, etc.)
   - CI/CD platform secrets (GitHub Secrets, GitLab CI/CD Variables, etc.)
   - Kubernetes Secrets (if containerized)

## Deployment Checklist

- [ ] APP_DEBUG set to `false`
- [ ] APP_KEY is a valid Laravel encryption key
- [ ] Database credentials are set (DB_USERNAME, DB_PASSWORD)
- [ ] Keycloak is configured (KEYCLOAK_CLIENT_ID, KEYCLOAK_CLIENT_SECRET)
- [ ] All `.env` files are excluded from version control
- [ ] HTTPS is enforced on production server
- [ ] Firewall rules restrict database access to application server only
- [ ] Redis/Cache server is on private network
- [ ] Regular backups are enabled for database
- [ ] Error logs are monitored for security issues
- [ ] Rate limiting thresholds are appropriate for expected load
- [ ] Keycloak instance has HTTPS enabled
- [ ] Keycloak admin credentials are secured
- [ ] API endpoints require authentication except `/api/v1/auth/login` and `/up`

## Production Environment Variables

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://hris-api.company.com

DB_CONNECTION=mysql
DB_HOST=<private-db-host>
DB_PORT=3306
DB_DATABASE=hris
DB_USERNAME=<secure-username>
DB_PASSWORD=<secure-password>

CACHE_STORE=redis
SESSION_DRIVER=redis

REDIS_HOST=<private-redis-host>
REDIS_PASSWORD=<secure-redis-password>
REDIS_PORT=6379

KEYCLOAK_BASE_URL=https://keycloak.company.com
KEYCLOAK_INTERNAL_BASE_URL=https://keycloak.company.com
KEYCLOAK_REALM=production
KEYCLOAK_CLIENT_ID=hris-api
KEYCLOAK_CLIENT_SECRET=<rotate-after-git-removal>
KEYCLOAK_REDIRECT_URI=https://hris-api.company.com/auth/callback

SANCTUM_EXPIRATION=43200
APP_TIMEZONE=Asia/Makassar
```

## Security Incident Response

### If Secrets Are Committed to Git
1. **Remove from history**: Use git-filter-branch or git-filter-repo
2. **Force push**: Update all branch refs (requires authorized access)
3. **Rotate secrets**: Generate new credentials in Keycloak and update `.env`
4. **Audit access**: Check git logs to see who had access to the secret
5. **Notify team**: Alert other developers of rotation requirement

### If Unauthorized Access is Detected
1. Rotate all sensitive credentials immediately
2. Review audit logs for suspicious activity
3. Check Keycloak admin logs for unauthorized changes
4. Revoke compromised tokens
5. Update rate limiting thresholds if needed
6. File incident report with security team

## Additional Resources
- [Laravel Security](https://laravel.com/docs/11.x/security)
- [Keycloak Security](https://www.keycloak.org/documentation)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
