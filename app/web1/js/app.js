function slideUp($el) {
    $el.addClass('js_boundaries').removeClass('js_hide')
        .slideUp(0, () => {
            $el.removeClass('js_boundaries')
        })
}

$(document).ready(() => {
    const $page_nav_mobile_panel = $('#page_nav_mobile_panel')
    const $page_nav_mobile_opener = $('#page_nav_mobile_opener')
    slideUp($page_nav_mobile_panel)

    let isNavOpen = false
    function openMenu() {
        $page_nav_mobile_opener.hide()
        $page_nav_mobile_panel.slideDown()
        isNavOpen = true
    }

    function closeMenu() {
        $page_nav_mobile_panel.slideUp(() => {
            $page_nav_mobile_opener.show()
        })
        isNavOpen = false
    }

    $page_nav_mobile_opener.click(() => {
        if (isNavOpen) {
            closeMenu()
        } else {
            openMenu()
        }
    })
    $('#page_nav_mobile_closer').click(() => {
        closeMenu()
    })
    $('#page_nav_mobile_click_away').click(() => {
        closeMenu()
    })
})