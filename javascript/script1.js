$(document).ready(function() {
    //verificacion de si esta logeado el usuario uwu
    $('.select-service').click(function(e) {
        e.preventDefault();
        
        //por ahora no esta logueado
        const isLoggedIn = false;
        
        if (!isLoggedIn) {
            $('#loginRequiredModal').modal('show');
        } else {
            const serviceType = $(this).data('service');
            //guardar el tipo de servicio en sessionstorage
            sessionStorage.setItem('selectedService', serviceType);
            //redirigir a la página de productos para seleccionar uno
            window.location.href = 'productos.html';
        }
    });
    
    //formulario de agendar servicio
    $('#bookingForm').submit(function(e) {
        e.preventDefault();
        $('#confirmationModal').modal('show');
    });
    
    //manejar formulario de login
    $('#loginForm').submit(function(e) {
        e.preventDefault();
        //simulacion de login exitoso
        sessionStorage.setItem('isLoggedIn', 'true');
        window.location.href = 'servicios.html';
    });
    
    //manejar formulario de registro
    $('#signupForm').submit(function(e) {
        e.preventDefault();
        //simulacion registro exitoso
        sessionStorage.setItem('isLoggedIn', 'true');
        window.location.href = 'servicios.html';
    });
    
    //manejar formulario de login de administrador
    $('#adminLoginForm').submit(function(e) {
        e.preventDefault();
        //simulacion de login exitoso
        sessionStorage.setItem('isAdmin', 'true');
        window.location.href = 'admin/index.html';
    });
    
    //cargar datos de servicio y producto
    if (window.location.pathname.includes('agendar.html')) {
        const serviceType = sessionStorage.getItem('selectedService');
        const selectedProduct = sessionStorage.getItem('selectedProduct');
        
        $('#serviceType').val(serviceType);
        $('#selectedProduct').val(selectedProduct);
    }
    
    //página de productos a servicios
    if (window.location.pathname.includes('productos.html') && sessionStorage.getItem('selectedService')) {
        $('.product-card').click(function() {
            const productName = $(this).find('.card-title').text();
            sessionStorage.setItem('selectedProduct', productName);
            window.location.href = 'agendar.html';
        });
    }
    
    //verificar si el usuario es admin al usar páginas de admin 
    if (window.location.pathname.includes('admin/')) {
        const isAdmin = sessionStorage.getItem('isAdmin');
        if (!isAdmin) {
            window.location.href = '../login.html';
        }
    }
});