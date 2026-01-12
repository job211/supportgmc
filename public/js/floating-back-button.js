/**
 * Floating Back Button - Always visible
 * Appears immediately on page load
 */

(function() {
    // Create floating back button
    const floatingBack = document.createElement('div');
    floatingBack.id = 'floating-back-button';
    floatingBack.className = 'floating-back-button show';
    floatingBack.innerHTML = '<a href="javascript:history.back()" class="btn-floating" title="Retour"><i class="fas fa-arrow-left"></i></a>';
    document.body.appendChild(floatingBack);

    let hideTimeout;

    // Hide after 3 seconds of no interaction
    function resetHideTimer() {
        clearTimeout(hideTimeout);
        floatingBack.classList.remove('hidden');
        
        hideTimeout = setTimeout(function() {
            floatingBack.classList.add('hidden');
        }, 3000);
    }

    // Initial hide timeout on page load
    resetHideTimer();

    // Reset timer on scroll
    window.addEventListener('scroll', resetHideTimer, { passive: true });

    // Reset timer on mousemove
    window.addEventListener('mousemove', resetHideTimer, { passive: true });

    // Reset timer on keyboard input
    window.addEventListener('keydown', resetHideTimer, { passive: true });

    // Reset timer on touch
    window.addEventListener('touchmove', resetHideTimer, { passive: true });

    // Show on touch when scrolled down
    window.addEventListener('touchmove', function() {
        if (window.scrollY > 300 && isVisible) {
            floatingBack.classList.remove('hidden');
        }
    }, { passive: true });

    // Reinit on page load
    window.addEventListener('load', function() {
        if (window.scrollY <= 300) {
            floatingBack.classList.remove('show');
            isVisible = false;
        }
    });
})();
