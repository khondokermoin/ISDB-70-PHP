

$(function () {

    // =========================
    // Disable Right Click
    // =========================
    $(document).on("contextmenu", function (e) {
        e.preventDefault();
    });

    // =========================
    // Disable Copy
    // =========================
    $(document).on("copy", function (e) {
        e.preventDefault();
    });

    // =========================
    // Disable Cut
    // =========================
    $(document).on("cut", function (e) {
        e.preventDefault();
    });

    // =========================
    // Disable Text Selection
    // =========================
    $(document).on("selectstart", function (e) {
        e.preventDefault();
    });

    // =========================
    // Disable Drag
    // =========================
    $(document).on("dragstart", function (e) {
        e.preventDefault();
    });

    // =========================
    // Disable Keyboard Shortcuts
    // =========================
    $(document).keydown(function (e) {

        // F12
        if (e.keyCode === 123) {
            e.preventDefault();
            return false;
        }

        // Ctrl + Shift + I
        if (e.ctrlKey && e.shiftKey && e.keyCode === 73) {
            e.preventDefault();
            return false;
        }

        // Ctrl + Shift + J
        if (e.ctrlKey && e.shiftKey && e.keyCode === 74) {
            e.preventDefault();
            return false;
        }

        // Ctrl + U
        if (e.ctrlKey && e.keyCode === 85) {
            e.preventDefault();
            return false;
        }

        // Ctrl + C
        if (e.ctrlKey && e.keyCode === 67) {
            e.preventDefault();
            return false;
        }

        // Ctrl + A
        if (e.ctrlKey && e.keyCode === 65) {
            e.preventDefault();
            return false;
        }

        // Ctrl + S
        if (e.ctrlKey && e.keyCode === 83) {
            e.preventDefault();
            return false;
        }

    });

});