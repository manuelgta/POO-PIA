<script src="javascript/bootstrap.bundle.min.js"></script>
<script src="javascript/jquery-3.5.1.min.js"></script>
<script src="javascript/script1.js"></script>
<script>
    $(document).ready(function () {
        setTimeout(function () {
            $(".auto-dismiss").fadeOut("slow", function () { // Para eliminar alertas de error y exito
                $(this).remove();
            });
        }, 7500); // 7.5 segundos

        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]'); // Tooltips de bootstrap
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    });
</script>