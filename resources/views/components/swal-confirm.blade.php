<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener("click", function(e) {

        const btn = e.target.closest(".swal-confirm");

        if (!btn) return;

        e.preventDefault();

        const form = btn.closest("form");

        const title = btn.dataset.title || "Apakah Anda yakin?";
        const text = btn.dataset.text || "Data ini akan diproses.";
        const confirm = btn.dataset.confirm || "Ya, lanjutkan";

        Swal.fire({
            title: title,
            text: text,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: confirm,
            cancelButtonText: "Batal"
        }).then((result) => {

            if (result.isConfirmed) {

                if (form) {
                    form.submit();
                }

            }

        });

    });
</script>
