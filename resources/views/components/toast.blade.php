@if(session('success') || session('error') || session('warning') || session('info'))

<div class="position-fixed top-0 end-0 p-3" style="z-index:1080">

    @if(session('success'))
    <div id="toastSuccess" class="toast align-items-center text-bg-success border-0 mb-2" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                {{ session('success') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div id="toastError" class="toast align-items-center text-bg-danger border-0 mb-2" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                {{ session('error') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    @endif

    @if(session('warning'))
    <div id="toastWarning" class="toast align-items-center text-bg-warning border-0 mb-2" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                {{ session('warning') }}
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    @endif

    @if(session('info'))
    <div id="toastInfo" class="toast align-items-center text-bg-info border-0 mb-2" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                {{ session('info') }}
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
