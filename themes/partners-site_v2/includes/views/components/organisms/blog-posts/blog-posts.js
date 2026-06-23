document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('#blog_pagination')) {
        $('.a-pagination').find('button').on('click', function (event) {
            var pageIndex = event.target.closest('button').dataset.paginationNumber;
            var currentUrl = window.location.href;
            var currentPageMatch = currentUrl.match(/page\/(\d+)/);

            if (currentPageMatch) {
                var nextPageUrl = currentUrl.replace(/page\/(\d+)/, 'page/' + pageIndex);
            } else {
                var nextPageUrl = currentUrl + 'page/' + pageIndex;
            }

            location.href = nextPageUrl;
        });
    }
});