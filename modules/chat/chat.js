$(function() {
    // minimal escape helper
    function escapeHtml(str) {
        return (str||'')
            .toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    var Chat = {
        updateTimer: null,

        init: function() {
            this.setupEventListeners();
            this.startUpdates();
        },

        setupEventListeners: function() {
            var self = this;
            $('#chat-form').on('submit', function(e) {
                e.preventDefault();
                var message = $('#mensaje-texto').val().trim();
                if (message === '') return;
                $('#mensaje-texto').prop('disabled', true);
                $.ajax({
                    url: 'api-app.php',
                    type: 'POST',
                    data: { module: 'chat', method: 'sendMessage', content: message },
                    dataType: 'json',
                    success: function(res) {
                        if (res && res.status === 1) {
                            $('#mensaje-texto').val('');
                            self.loadMessages();
                        } else {
                            alert(res && res.msg ? res.msg : 'Error al enviar mensaje');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al enviar:', xhr.responseText);
                        alert('Error al enviar mensaje (ver consola)');
                    },
                    complete: function() { $('#mensaje-texto').prop('disabled', false).focus(); }
                });
            });

            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    if (Chat.updateTimer) clearInterval(Chat.updateTimer);
                } else {
                    Chat.startUpdates();
                }
            });
        },

        loadMessages: function() {
            $.ajax({
                url: 'api-app.php',
                type: 'GET',
                data: { module: 'chat', method: 'getChatMessages' },
                dataType: 'json',
                success: function(res) {
                    if (res && res.status === 1 && Array.isArray(res.messages)) {
                        var html = '';
                        if (res.messages.length === 0) {
                            html = '<tr><td colspan="3" class="text-center">No hay mensajes</td></tr>';
                        } else {
                            res.messages.forEach(function(m) {
                                html += '<tr>' +
                                        '<td>' + escapeHtml(m.user) + '</td>' +
                                        '<td>' + escapeHtml(m.content) + '</td>' +
                                        '<td>' + escapeHtml(m.date) + '</td>' +
                                        '</tr>';
                            });
                        }
                        $('#chat-messages tbody').html(html);
                        var chatDiv = document.getElementById('chat-messages');
                        if (chatDiv) chatDiv.scrollTop = chatDiv.scrollHeight;
                    }
                },
                error: function(xhr) {
                    console.error('Error cargando mensajes:', xhr.responseText);
                }
            });
        },

        startUpdates: function() {
            this.loadMessages();
            this.updateTimer = setInterval(this.loadMessages, 10000);
        }
    };

    Chat.init();
});
