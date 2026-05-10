<?php /* Dark mode init — include in <head> on pages that skip header.php */ ?>
<link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<script>
(function(){
    if(localStorage.getItem('auralib_theme')==='dark')
        document.documentElement.classList.add('dark');
})();
</script>