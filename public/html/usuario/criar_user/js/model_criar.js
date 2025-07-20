document.addEventListener("DOMContentLoaded", function () {
    let btn = document.getElementById("btnCriarUsuario");
    let form = document.getElementById("formUsuario");

    btn.addEventListener("click", function () {
        if (form.classList.contains("mostrar")) {
            form.style.opacity = "0";
            setTimeout(() => {
                form.classList.remove("mostrar");
                form.style.display = "none";
            }, 400); // Tempo igual ao do CSS
        } else {
            form.style.display = "block";
            setTimeout(() => {
                form.classList.add("mostrar");
                form.style.opacity = "1";
            }, 10);
        }
    });
});