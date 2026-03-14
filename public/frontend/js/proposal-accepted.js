/**
 * proposal-accepted.js
 * Confetti canvas animation for the proposal accepted page
 */

(function () {
    'use strict';

    const canvas = document.getElementById('paConfetti');
    if (!canvas) return;

    const ctx    = canvas.getContext('2d');
    let pieces   = [];
    let animId;

    const COLORS = ['#1A56F0', '#0DBD7F', '#E8A838', '#F04060', '#4D78F5', '#fff'];
    const COUNT  = 120;

    function resize() {
        canvas.width  = window.innerWidth;
        canvas.height = window.innerHeight;
    }

    function randomBetween(a, b) {
        return a + Math.random() * (b - a);
    }

    function createPiece() {
        return {
            x:       randomBetween(0, canvas.width),
            y:       randomBetween(-canvas.height, 0),
            w:       randomBetween(6, 12),
            h:       randomBetween(10, 18),
            color:   COLORS[Math.floor(Math.random() * COLORS.length)],
            vx:      randomBetween(-1.5, 1.5),
            vy:      randomBetween(2, 5),
            opacity: randomBetween(0.7, 1),
            angle:   randomBetween(0, Math.PI * 2),
            spin:    randomBetween(-0.08, 0.08),
        };
    }

    function init() {
        resize();
        pieces = Array.from({ length: COUNT }, createPiece);
        animate();
    }

    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        pieces.forEach((p, i) => {
            p.x     += p.vx;
            p.y     += p.vy;
            p.angle += p.spin;
            p.vy    += 0.04; // gravity

            if (p.y > canvas.height + 20) {
                pieces[i] = createPiece();
                pieces[i].y = -20;
            }

            ctx.save();
            ctx.translate(p.x, p.y);
            ctx.rotate(p.angle);
            ctx.globalAlpha = p.opacity;
            ctx.fillStyle   = p.color;
            ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
            ctx.restore();
        });

        animId = requestAnimationFrame(animate);

        // Stop after 5 seconds
        setTimeout(() => {
            cancelAnimationFrame(animId);
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }, 5000);
    }

    window.addEventListener('resize', resize);
    window.addEventListener('load',   init);

})();