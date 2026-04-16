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
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var postId = form.getAttribute('data-post-id');

            ajaxRequest(
                {
                    action: 'publicacoes_like',
                    nonce: PublicacoesSubmissao.nonce,
                    post_id: postId,
                },
                function (data) {
                    var button = form.querySelector('.publicacoes-like-button');
                    if (button) {
                        button.textContent = '❤️ ' + data.likes;
                        button.classList.add('publicacoes-heart-pulse');
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
                button.innerHTML = '<span class="publicacoes-action-icon">💬</span> Comentar <span class="publicacoes-comment-count">(' + commentCount + ')</span>';
            } else {
                button.innerHTML = '<span class="publicacoes-action-icon">💬</span> Ocultar comentários';
            }
        });
    });

    var readMoreButtons = document.querySelectorAll('.publicacoes-read-more');
    readMoreButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var card = button.closest('.publicacoes-card');
            var more = card.querySelector('.publicacoes-caption-more');
            var expanded = more.style.display === 'block';

            if (more) {
                more.style.display = expanded ? 'none' : 'block';
                button.textContent = expanded ? 'Leia mais' : 'Mostrar menos';
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
