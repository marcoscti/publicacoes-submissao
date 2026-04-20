document.addEventListener('DOMContentLoaded', function () {
    var likeForms = document.querySelectorAll('.publicacoes-like-form');
    var commentForms = document.querySelectorAll('.publicacoes-comment-form');

    function ajaxRequest(data, callback, errorCallback) {
        fetch(PublicacoesSubmissao.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            },
            body: new URLSearchParams(data).toString(),
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (json) {
                if (json.success) {
                    callback(json.data);
                } else {
                    errorCallback(json.data.message || 'Erro');
                }
            })
            .catch(function () {
                errorCallback('Erro de conexão.');
            });
    }

    likeForms.forEach(function (form) {
        var button = form.querySelector('.publicacoes-like-button');
        var postId = form.getAttribute('data-post-id');

        // Verificar se já foi curtido nessa sessão
        var likedPosts = JSON.parse(localStorage.getItem('publicacoes_liked') || '{}');
        if (likedPosts[postId]) {
            button.disabled = true;
            button.classList.add('publicacoes-liked-disabled');
            button.title = 'Você já curtiu este post nesta sessão';
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            // Verificar novamente se já foi curtido
            if (likedPosts[postId]) {
                console.log('Este post já foi curtido nesta sessão');
                return;
            }

            ajaxRequest(
                {
                    action: 'publicacoes_like',
                    nonce: PublicacoesSubmissao.nonce,
                    post_id: postId,
                },
                function (data) {
                    if (button) {
                        button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16px" height="14px" viewBox="0 0 16 14" version="1.1"><g id="surface1"><path style="fill:none;stroke-width:0.264583;stroke-linecap:butt;stroke-linejoin:miter;stroke:rgb(74.901962%,33.333334%,38.431373%);stroke-opacity:1;stroke-miterlimit:4;" d="M 1.247469 0.132292 C 0.516764 0.132292 0.10542 0.753442 0.133325 1.450041 C 0.416512 2.340942 1.138949 3.101619 2.137337 3.641121 C 3.091284 3.077848 3.781681 2.388485 4.016292 1.617472 C 4.020427 1.60507 4.024561 1.593701 4.027661 1.581299 C 4.03903 1.543058 4.054533 1.504818 4.062801 1.466577 L 4.059701 1.466577 C 4.075204 1.393197 4.082438 1.318783 4.082438 1.244368 C 4.082438 0.637687 3.590479 0.144694 2.983797 0.144694 C 2.646867 0.144694 2.329574 0.299723 2.120801 0.563273 C 1.913062 0.291455 1.589567 0.132292 1.247469 0.132292 Z M 1.247469 0.132292 " transform="matrix(3.779527,0,0,3.779527,0,0.000000307798)" /></g></svg> ' + data.likes;
                        button.classList.add('publicacoes-heart-pulse');

                        // Marcar como curtido no localStorage
                        likedPosts[postId] = true;
                        localStorage.setItem('publicacoes_liked', JSON.stringify(likedPosts));

                        // Desabilitar botão após curtida
                        button.disabled = true;
                        button.classList.add('publicacoes-liked-disabled');
                        button.title = 'Você já curtiu este post nesta sessão';

                        setTimeout(function () {
                            button.classList.remove('publicacoes-heart-pulse');
                        }, 600);
                    }
                },
                function (message) {
                    console.error('Erro ao curtir:', message);
                }
            );
        });
    });

    var toggleButtons = document.querySelectorAll('.publicacoes-toggle-comments');
    toggleButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var card = button.closest('.publicacoes-card');
            var form = card.querySelector('.publicacoes-comment-form');
            var commentsList = card.querySelector('.publicacoes-comments-list');
            var expanded = button.getAttribute('aria-expanded') === 'true';

            if (form) {
                form.style.display = expanded ? 'none' : 'block';
            }
            if (commentsList) {
                commentsList.style.display = expanded ? 'none' : 'block';
            }
            button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            if (expanded) {
                var commentItems = commentsList.querySelectorAll('.publicacoes-comment-item');
                var commentCount = commentItems.length;
                button.innerHTML = '<span class="publicacoes-action-icon"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="16px" height="14px" viewBox="0 0 16 14" version="1.1"><g id="surface1"><path style="fill:none;stroke-width:0.231296;stroke-linecap:butt;stroke-linejoin:miter;stroke:rgb(74.901962%,33.333334%,38.431373%);stroke-opacity:1;stroke-miterlimit:4;" d="M 2.189014 0.120923 C 1.266073 0.120923 0.516764 0.880566 0.516764 1.817977 C 0.516764 2.078426 0.576709 2.335775 0.690397 2.569352 L 0.572575 3.01687 L 0.412378 3.623551 L 1.018026 3.458187 L 1.451074 3.340365 C 1.680518 3.455086 1.932699 3.513997 2.189014 3.513997 C 3.111955 3.513997 3.861263 2.754354 3.861263 1.817977 C 3.861263 0.880566 3.111955 0.120923 2.189014 0.120923 Z M 2.189014 0.120923 " transform="matrix(3.779527,0,0,3.779527,0,0.000000307798)" /></g></svg></span> <span class="publicacoes-comment-count">' + commentCount + '</span>';
            }
        });
    });

    var readMoreButtons = document.querySelectorAll('.publicacoes-read-more');
    readMoreButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var card = button.closest('.publicacoes-card');
            var shortText = card.querySelector('.publicacoes-caption-short');
            var more = card.querySelector('.publicacoes-caption-more');
            var expanded = more.style.display === 'block';

            if (more && shortText) {
                shortText.style.display = expanded ? 'block' : 'none';
                more.style.display = expanded ? 'none' : 'block';
                button.textContent = expanded ? 'Leia mais' : 'Recolher';
            }
        });
    });

    commentForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var postId = form.getAttribute('data-post-id');
            var authorInput = form.querySelector('input[name="author"]');
            var emailInput = form.querySelector('input[name="email"]');
            var commentInput = form.querySelector('textarea[name="comment"]');
            var messageNode = form.querySelector('.publicacoes-comment-message');
            var commentsList = form.closest('.publicacoes-card').querySelector('.publicacoes-comments-list');

            if (!authorInput.value.trim() || !emailInput.value.trim() || !commentInput.value.trim()) {
                if (messageNode) {
                    messageNode.textContent = 'Todos os campos são obrigatórios.';
                }
                return;
            }

            ajaxRequest(
                {
                    action: 'publicacoes_comment',
                    nonce: PublicacoesSubmissao.nonce,
                    post_id: postId,
                    author: authorInput.value.trim(),
                    email: emailInput.value.trim(),
                    comment: commentInput.value.trim(),
                },
                function (data) {
                    if (messageNode) {
                        messageNode.textContent = data.message;
                    }
                    if (commentsList && data.html) {
                        var placeholder = commentsList.querySelector('p');
                        if (placeholder && placeholder.textContent === 'Seja o primeiro a comentar.') {
                            commentsList.innerHTML = '';
                        }
                        commentsList.insertAdjacentHTML('beforeend', data.html);
                    }
                    form.reset();

                    // Update comment count in button
                    var card = form.closest('.publicacoes-card');
                    var toggleButton = card.querySelector('.publicacoes-toggle-comments');
                    if (toggleButton) {
                        var commentItems = commentsList.querySelectorAll('.publicacoes-comment-item');
                        var currentCount = commentItems.length;
                        toggleButton.innerHTML = '<span class="publicacoes-action-icon">💬</span> Comentar <span class="publicacoes-comment-count">(' + currentCount + ')</span>';
                    }
                },
                function (message) {
                    if (messageNode) {
                        messageNode.textContent = message;
                    }
                }
            );
        });
    });
});
