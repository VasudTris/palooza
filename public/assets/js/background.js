document.addEventListener('DOMContentLoaded', function () {
    const circles = document.querySelectorAll('.circles li');
    circles.forEach(circle => {
        const leftPercentage = Math.random() * 100;
        const size = Math.floor(Math.random() * (150 - 20 + 1)) + 20;
        const animationDuration = Math.floor(Math.random() * (30 - 15 + 1)) + 15;   
        const rotateDirection = Math.random() < 0.5 ? 1 : -1; 
        const rotateValue = rotateDirection * Math.floor(Math.random() * 360);       
        circle.style.left = `${leftPercentage}%`;
        circle.style.width = `${size}px`;
        circle.style.height = `${size}px`;      
        circle.style.setProperty('--rotate', `${rotateValue}deg`); 
        circle.style.animation = `animate ${animationDuration}s linear infinite`;
    });
});