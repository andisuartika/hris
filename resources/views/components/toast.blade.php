@if(session('success') || session('error') || session('warning') || session('info'))

<div class="position-fixed top-0 end-0 p-3" style="z-index:1080">

    @if(session('success'))
    <div class="toast align-items-center text-bg-success border-0 mb-2" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                {{ session()->pull('success') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="toast align-items-center text-bg-danger border-0 mb-2" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                {{ session()->pull('error') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    @endif

    @if(session('warning'))
    <div class="toast align-items-center text-bg-warning border-0 mb-2" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                {{ session()->pull('warning') }}
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    @endif

    @if(session('info'))
    <div class="toast align-items-center text-bg-info border-0 mb-2" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                {{ session()->pull('info') }}
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    @endif

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const toastElList = document.querySelectorAll('.toast');

        toastElList.forEach(function(toastEl) {

            const toast = new bootstrap.Toast(toastEl, {
                delay: 3000
            });

            toast.show();

        });

    });
</script>

@endif
