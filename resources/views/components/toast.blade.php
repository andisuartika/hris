@if(session('success') || session('error') || session('warning') || session('info'))

<div class="position-fixed top-0 end-0 p-3" style="z-index:1080">

    @if(session('success'))
    <div class="toast text-bg-success mb-2" data-toast="success">
        <div class="d-flex">
            <div class="toast-body">
                {{ session('success') }}
            </div>
            <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="toast text-bg-danger mb-2" data-toast="error">
        <div class="d-flex">
            <div class="toast-body">
                {{ session('error') }}
            </div>
            <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    @endif

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        // Cegah double trigger
        if (window.toastAlreadyShown) return;
        window.toastAlreadyShown = true;

        document.querySelectorAll('.toast').forEach(function(toastEl) {
            new bootstrap.Toast(toastEl, {
                delay: 3000
            }).show();
        });

    });
</script>

@endif
