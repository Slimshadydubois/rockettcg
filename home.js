function slideCarousel(carouselId, direction) {
    const carousel = document.getElementById(carouselId);
    const scrollAmount = 300; // Aproximadamente a largura de um card + gap
    
    if (carousel) {
        carousel.scrollBy({
            left: direction * scrollAmount,
            behavior: 'smooth'
        });
    }
}

// Lógica do Hero Slider
let currentHeroSlide = 0;
const totalHeroSlides = 4; // Principal + 3 Expansões

function updateHeroIndicators() {
    const indicators = document.querySelectorAll('#heroIndicators .indicator');
    indicators.forEach((ind, index) => {
        if (index === currentHeroSlide) {
            ind.classList.add('active');
        } else {
            ind.classList.remove('active');
        }
    });
}

function slideHero(direction) {
    currentHeroSlide += direction;
    if (currentHeroSlide < 0) currentHeroSlide = totalHeroSlides - 1;
    if (currentHeroSlide >= totalHeroSlides) currentHeroSlide = 0;
    goToHeroSlide(currentHeroSlide);
}

function goToHeroSlide(index) {
    const slider = document.getElementById('heroSlider');
    if (slider) {
        currentHeroSlide = index;
        const slideWidth = slider.clientWidth;
        slider.scrollTo({
            left: currentHeroSlide * slideWidth,
            behavior: 'smooth'
        });
        updateHeroIndicators();
    }
}

// Atualizar indicador baseado no scroll manual e Auto-Slide
document.addEventListener('DOMContentLoaded', () => {
    const slider = document.getElementById('heroSlider');
    let autoSlideInterval;
    
    function startAutoSlide() {
        autoSlideInterval = setInterval(() => {
            slideHero(1);
        }, 5000);
    }
    
    if (slider) {
        slider.addEventListener('scroll', () => {
            const slideWidth = slider.clientWidth;
            const scrollLeft = slider.scrollLeft;
            const newIndex = Math.round(scrollLeft / slideWidth);
            if (newIndex !== currentHeroSlide && newIndex >= 0 && newIndex < totalHeroSlides) {
                currentHeroSlide = newIndex;
                updateHeroIndicators();
            }
        });
        
        startAutoSlide();
    }
});
