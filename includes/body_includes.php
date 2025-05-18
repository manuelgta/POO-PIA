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

        $('[data-cartRemove]').on('click', function () {
            let self = $(this);
            let criteria = 'si';
            let data = self.attr('data-cartRemove');
            $.ajax({
                url: "php/globalSetSession.php",
                type: "POST",
                data: {
                    criteria: criteria,
                    data: data,
                    unset: "true",
                    page: "cartRemove"
                },
                success: function(response) {
                    window.location.reload();
                },
                error: function(response) {
                    alert("Error al procesar el evento");
                    console.log(response);
                }
            });
        });

        $('[data-cartChange]').on('click', function () {
            let self = $(this);
            let criteria = self.attr('data-cartChange');
            let data = self.val();
            $.ajax({
                url: "php/globalSetSession.php",
                type: "POST",
                data: {
                    criteria: criteria,
                    data: data,
                    unset: "false",
                    page: "cartChange"
                },
                success: function(response) {
                    
                },
                error: function(response) {
                    alert("Error al procesar el evento");
                    console.log(response);
                }
            });
        });
    });
</script>