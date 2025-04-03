<script>
    document.addEventListener('DOMContentLoaded', function () {
        const menuButton = document.createElement('button');
        menuButton.className = 'admin-menu-button';
        menuButton.innerHTML = '<i class="fas fa-bars"></i>';
        document.querySelector('.admin-header').prepend(menuButton);

        menuButton.addEventListener('click', function () {
            document.querySelector('.admin-sidebar').classList.toggle('active');
        });
    });
</script>