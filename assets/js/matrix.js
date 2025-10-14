console.log('Matrix script loaded');

const canvas = document.getElementById('matrix');
if (!canvas) {
    console.error('Canvas not found');
    throw new Error('Canvas element not found');
}
const ctx = canvas.getContext('2d');

// Ajustar el canvas al tamaño de la ventana
function setCanvasSize() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
}
setCanvasSize();
window.addEventListener('resize', setCanvasSize);

// Caracteres para la animación (más densos)
const chars = '01アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲンABCDEFGHIJKLMNOPQRSTUVWXYZ';
const charArray = chars.split('');

const fontSize = 12; // Caracteres más pequeños
const columns = canvas.width / fontSize;

// Array para mantener la posición Y de cada columna
const drops = [];
for (let i = 0; i < columns; i++) {
    drops[i] = Math.floor(Math.random() * canvas.height/fontSize); // Inicio aleatorio
}

// Crear gradiente para los caracteres
function getGradientColor(y) {
    const ratio = y / canvas.height;
    const startColor = { r: 102, g: 126, b: 234 }; // #667eea
    const endColor = { r: 118, g: 75, b: 162 };    // #764ba2
    
    return `rgb(${
        Math.floor(startColor.r + (endColor.r - startColor.r) * ratio)},${
        Math.floor(startColor.g + (endColor.g - startColor.g) * ratio)},${
        Math.floor(startColor.b + (endColor.b - startColor.b) * ratio)})`;
}

// Función de dibujo
function draw() {
    ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    ctx.font = fontSize + 'px monospace';
    
    for (let i = 0; i < drops.length; i++) {
        const text = charArray[Math.floor(Math.random() * charArray.length)];
        const y = drops[i] * fontSize;
        
        ctx.fillStyle = getGradientColor(y);
        ctx.fillText(text, i * fontSize, y);
        
        if (y > canvas.height && Math.random() > 0.98) {
            drops[i] = 0;
        }
        
        drops[i]++;
    }
}

// Animar (más rápido)
setInterval(draw, 30);
