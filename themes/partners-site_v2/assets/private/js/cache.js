import axios from 'axios';
import qs from "qs";

document.addEventListener('DOMContentLoaded', () => {
    const button = document.querySelector('#wpadminbar #wp-admin-bar-purge-cache');
    if (button) {
        button.addEventListener('click', e => {
            e.preventDefault();

            const data = {
                'action': 'purgeCache',
            };

            axios({
                method: 'POST',
                headers: {'content-type': 'application/x-www-form-urlencoded'},
                url: '/wp/wp-admin/admin-ajax.php',
                data: qs.stringify(data)
            }).then(response => {
                const id = 'cache-notice';

                if (!document.getElementById(id)) {
                    const notice = document.createElement('div');
                    notice.id = id;
                    notice.innerHTML = '<p>Wyczyszczono cache strony</p>';
                    document.querySelector('#wp-admin-bar-purge-cache').appendChild(notice)

                    setTimeout(()=>{
                        notice.classList.add('is-hidden')
                        setTimeout(()=>{
                            notice.remove()
                        },400)
                    }, 3000)
                }
            }).catch(error => {
                console.error(error);
            });
        })
    }
})