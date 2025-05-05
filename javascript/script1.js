$(document).ready(function() {
    //verificacion de si esta logeado el usuario uwu
    $('.select-service').click(function(e) {
        e.preventDefault();
        
        //por ahora esta logueado
        const isLoggedIn = true;
        
        if (!isLoggedIn) {
            $('#loginRequiredModal').modal('show');
        } else {
            const serviceType = $(this).data('service');
            //guardar el tipo de servicio en sessionstorage
            sessionStorage.setItem('selectedService', serviceType);
            //redirigir a la página de productos para seleccionar uno
            window.location.href = 'productos.php';
        }
    });
    
    //formulario de agendar servicio
    $('#bookingForm').submit(function(e) {
        e.preventDefault();
        $('#confirmationModal').modal('show');
    });
    
    // //manejar formulario de login
    // $('#loginForm').submit(function(e) {
    //     e.preventDefault(); 
    
    //     const correo = $('#loginEmail').val();
    //     const password = $('#loginPassword').val();

    //     const formData = new FormData();
    //     formData.append('correo', correo);
    //     formData.append('password', password);
    
    //     $.ajax({
    //         url: 'login.php',  
    //         type: 'POST',
    //         data: formData,
    //         processData: false,
    //         contentType: false,
    //         success: function(response) {
    //             if (response.trim() === 'ok') {
    //                 sessionStorage.setItem('isLoggedIn', 'true');
    //                 window.location.href = 'servicios.html';
    //             } else {
    //                 $('#loginError').text(response); 
    //                 $('#loginError').css('color', 'red'); 
    //                 $('#loginError').show();
    //             }
    //         },
    //         error: function() {
    //             $('#loginError').text('Hubo un error al procesar la solicitud.');
    //         }
    //     });
    // });
    
    // //manejar formulario de registro
    // $('#signupForm').submit(function(e) {
    //     e.preventDefault(); 

    //     $.ajax({
    //     url: 'register.php',
    //     type: 'POST',
    //     data: $(this).serialize(),
    //     success: function(respuesta) {
    //         if (respuesta.trim() === "ok") {
    //             window.location.href = 'servicios.html'; 
    //         } else {
    //             alert("Error al registrar. Inténtalo de nuevo.");
    //         }
    //     },
    //     error: function() {
    //         alert("Error de conexión con el servidor.");
    //     }
    // });
    // });
    
    //manejar formulario de login de administrador
    $('#adminLoginForm').submit(function(e) {
        e.preventDefault();
        //simulacion de login exitoso
        sessionStorage.setItem('isAdmin', 'true');
        window.location.href = 'admin/index.php';
    });
    
    //cargar datos de servicio y producto
    if (window.location.pathname.includes('agendar.php')) {
        const serviceType = sessionStorage.getItem('selectedService');
        const selectedProduct = sessionStorage.getItem('selectedProduct');
        
        $('#serviceType').val(serviceType);
        $('#selectedProduct').val(selectedProduct);
    }
    
    //página de productos a servicios
    if (window.location.pathname.includes('productos.php') && sessionStorage.getItem('selectedService')) {
        $('.product-card').click(function() {
            const productName = $(this).find('.card-title').text();
            sessionStorage.setItem('selectedProduct', productName);
            window.location.href = 'agendar.php';
        });
    }
    
    //verificar si el usuario es admin al usar páginas de admin 
    if (window.location.pathname.includes('admin/')) {
        const isAdmin = sessionStorage.getItem('isAdmin');
        if (!isAdmin) {
            window.location.href = '../login.php';
        }
    }
});