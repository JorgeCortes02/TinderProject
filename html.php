<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slider</title>
    <link rel="stylesheet" href="styles.css">
    <style> 

/* styles.css */
/* styles.css */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-color: #f4f4f4;
}

.slider-container {
    position: relative;
    width: 800px;
    height: 400px;
    overflow: hidden;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.slider {
    display: flex;
    transition: transform 0.5s ease-in-out;
    will-change: transform;
}

.slide {
    min-width: 100%;
    flex-shrink: 0;
}

img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.prev-btn,
.next-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background-color: rgba(0, 0, 0, 0.5);
    color: #fff;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    font-size: 18px;
    border-radius: 5px;
    z-index: 10;
}

.prev-btn {
    left: 10px;
}

.next-btn {
    right: 10px;
}

.prev-btn:hover,
.next-btn:hover {
    background-color: rgba(0, 0, 0, 0.8);
}
    </style>


</head>
<body>
    <div class="slider-container">
        <div class="slider">
            
                <img class = "slide active" src="/images/hombre1.jpg" alt="Slide 1">
            
           
                <img class="slide" src="/images/hombre2.jpg" alt="Slide 2">
           
            
                <img class="slide" src="/images/hombre13.jpg" alt="Slide 1">
           
        </div>
        <button class="prev-btn">&lt;</button>
        <button class="next-btn">&gt;</button>
    </div>

    <script>

       // script.js
// script.js
const slides = document.querySelectorAll('.slide');
const slider = document.querySelector('.slider');
const prevBtn = document.querySelector('.prev-btn');
const nextBtn = document.querySelector('.next-btn');
let currentIndex = 0;

// Función para mostrar el slide actual
function showSlide(index) {
    const offset = -index * 100; // Calcula el desplazamiento en porcentaje
    slider.style.transform = `translateX(${offset}%)`;
}

// Configurar los botones
prevBtn.addEventListener('click', () => {
    currentIndex = (currentIndex === 0) ? slides.length - 1 : currentIndex - 1;
    showSlide(currentIndex);
});

nextBtn.addEventListener('click', () => {
    currentIndex = (currentIndex === slides.length - 1) ? 0 : currentIndex + 1;
    showSlide(currentIndex);
});

// Inicializar el slider
showSlide(currentIndex);

// Cambiar automáticamente cada 5 segundos
setInterval(() => {
    currentIndex = (currentIndex === slides.length - 1) ? 0 : currentIndex + 1;
    showSlide(currentIndex);
}, 5000);
    </script>
</body>
</html>