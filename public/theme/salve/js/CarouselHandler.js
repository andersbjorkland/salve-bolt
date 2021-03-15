const CarouselHandler = (images) => {
    console.log("WAS IMPORTED!!!");
    const carousel = document.getElementById("carousel");
    console.log(carousel);
    console.log(images);

    const carouselText = document.getElementById("carousel-text");

    const imageSources = [ ...images];

    let image = document.createElement("img");
    image.setAttribute("src", imageSources[0].path);
    image.setAttribute("alt", imageSources[0].alt);
    image.setAttribute("class", "");
    carousel.appendChild(image);

    carouselText.textContent = imageSources[0].alt;

    let imageIndex = 0;

    const changeImage = () => {
        if (image.getAttribute("class").localeCompare("fadeout") !== 0) {
            image.setAttribute("class", "fadeout");
            carouselText.textContent = "";
            if (imageIndex + 1 >= imageSources.length) {
                imageIndex = 0;
            } else {
                imageIndex++;
            }
            setTimeout(() => {
                image.setAttribute("src", imageSources[imageIndex].path);
                image.setAttribute("alt", imageSources[imageIndex].alt);
                carouselText.textContent = "";
                image.setAttribute("id", "showcase-img-" + imageIndex );
            }, 500);
            setTimeout(() => {
                image.setAttribute("class", "");
                carouselText.textContent = imageSources[imageIndex].alt;

            }, 900)
        }
    }

    return changeImage;
}




export default CarouselHandler;