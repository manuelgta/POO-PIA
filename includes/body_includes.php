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

        let timer;

        $('.product-description').hover( // Text-truncate en el carrito de compras
            function () {
                const $el = $(this);
                timer = setTimeout(function () {
                    $el.removeClass('text-truncate');
                }, 1000); // 1 segundos
            },
            function () {
                clearTimeout(timer);
                $(this).addClass('text-truncate');
            }
        );

        $('.zoomable-img').on('click', function () { // Zoom para imagenes con esta clase
            const imgSrc = $(this).attr('src');
            $('#modalImage').attr('src', imgSrc);
            $('#imageModal').modal('show');
        });
    });
</script>