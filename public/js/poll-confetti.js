function firePollConfetti(buttonEl, accentColor) {
    if (typeof confetti !== 'function') return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var rect = buttonEl.getBoundingClientRect();
    var x = (rect.left + rect.width / 2) / window.innerWidth;
    var y = (rect.top + rect.height / 2) / window.innerHeight;
    var colors = [accentColor, '#FFD700', '#FF6B6B', '#4ECDC4', '#45B7D1'];
    confetti({ particleCount: 50, spread: 55, origin: { x: x, y: y }, colors: colors,
               startVelocity: 30, gravity: 0.8, ticks: 150, scalar: 0.9, zIndex: 9999 });
    setTimeout(function() {
        confetti({ particleCount: 35, spread: 80, origin: { x: x, y: Math.max(0, y - 0.05) }, colors: colors,
                   startVelocity: 25, gravity: 0.8, ticks: 150, scalar: 0.8, zIndex: 9999 });
    }, 150);
}
