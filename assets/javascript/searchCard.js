
window.searchCard = function () {
    // Declare variables
    var input, filter, ul, li, a, i, txtValue;
    input = document.getElementById('myInput');

    filter = input.value.toUpperCase();
    carte = document.getElementsByClassName("carte");

    // Loop through all list items, and hide those who don't match the search query
    for (i = 0; i < carte.length; i++) {
        a = carte[i].getElementsByClassName("card-sl")[0];;

        txtValue = a.textContent || a.innerText;

        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            carte[i].classList.remove("d-none")
        } else {
            carte[i].classList.add("d-none");
        }
    }
}
