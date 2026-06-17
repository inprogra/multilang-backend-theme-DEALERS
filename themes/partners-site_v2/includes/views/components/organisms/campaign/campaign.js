var apiFetch = null;

$(document).on("click", ".editor-post-publish-button", function () {
/*    if (apiFetch == null) {
        var apiFetch = wp.apiFetch;
        apiFetch.use((options, next) => {
            var slugBefore = undefined;
            if (options && options.data.slug !== undefined) {
                slugBefore = options.data.slug;
            }
            const result = next(options);
            result.then((data) => {
                if (data.status == "publish" && slugBefore != undefined && data.slug != slugBefore) {
                    wp.data.dispatch('core/notices').createNotice(
                        'warning',
                        'Istnieje kampania globalna o slugu: "' + slugBefore + '". Slug Twojego wpisu zostal zamieniony na "' + data.slug + '".',
                        {
                            id: 'slug-changed-notice',
                            isDismissible: true,
                        }
                    );
                }
            });
            return result;
        });
    }*/
});
