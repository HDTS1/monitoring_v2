const canvas = document.getElementById("snowCanvas");
const ctx = canvas.getContext("2d");

// Nastav veľkosť canvasu na celú obrazovku
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

// Snehové vločky
const snowflakes = [];

// Funkcia na vytvorenie novej vločky
function createSnowflake() {
    return {
        x: Math.random() * canvas.width,   // Náhodná x pozícia
        y: Math.random() * canvas.height,  // Náhodná y pozícia
        radius: Math.random() * 6 + 2,     // Náhodná veľkosť (2 - 8 px)
        speed: Math.random() * 3 + 1,      // Náhodná rýchlosť
        wind: Math.random() * 2 - 1        // Náhodný horizontálny pohyb
    };
}

// Vytvorenie počiatočných vločiek
for (let i = 0; i < 150; i++) {
    snowflakes.push(createSnowflake());
}

// Funkcia na kreslenie vločky
function drawCustomSnowflake(ctx, x, y, radius) {
    ctx.save();
    ctx.translate(x, y);
    ctx.beginPath();
    for (let i = 0; i < 6; i++) { // Šesť ramien vločky
        ctx.moveTo(0, 0);
        ctx.lineTo(0, -radius);
        ctx.translate(0, -radius / 2);
        ctx.lineTo(-radius / 4, -radius / 4);
        ctx.moveTo(0, -radius / 2);
        ctx.lineTo(radius / 4, -radius / 4);
        ctx.translate(0, radius / 2);
        ctx.rotate(Math.PI / 3); // Rotácia o 60 stupňov
    }
    ctx.closePath();
    ctx.strokeStyle = "white";
    ctx.lineWidth = 1.5;
    ctx.stroke();
    ctx.restore();
}

// Funkcia na aktualizáciu pozície vločky
function updateSnowflake(snowflake) {
    snowflake.y += snowflake.speed;  // Pohyb nadol
    snowflake.x += snowflake.wind;  // Horizontálny pohyb

    // Reset pozície, ak vločka padne mimo obrazovky
    if (snowflake.y > canvas.height) {
        snowflake.y = -snowflake.radius;
        snowflake.x = Math.random() * canvas.width;
    }

    // Uprav horizontálnu pozíciu, ak vločka ide mimo obrazovky
    if (snowflake.x > canvas.width || snowflake.x < 0) {
        snowflake.x = Math.random() * canvas.width;
    }
}

// Hlavná funkcia na animáciu
function animate() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    snowflakes.forEach((snowflake) => {
        updateSnowflake(snowflake);
        drawCustomSnowflake(ctx, snowflake.x, snowflake.y, snowflake.radius);
    });

    requestAnimationFrame(animate); // Rekurzívna animácia
}

// Spustenie animácie
animate();

// Prepočítanie veľkosti canvasu pri zmene veľkosti okna
window.addEventListener("resize", () => {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
});
