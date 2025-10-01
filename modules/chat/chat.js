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

        // simple enterprise color palette
        palette: [
            '#1F4E79', // dark blue
            '#2A73B7', // blue
            '#1B6F5A', // teal green
            '#2E8B57', // sea green
            '#4F6D7A', // slate
            '#6C7A89', // gray-blue
            '#8C6D3F', // warm bronze
            '#3B3F44'  // charcoal
        ],

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
                            // Keep track of seen users for deterministic color mapping
                            var userColors = {};
                            function userColor(name) {
                                if (!name) return '#888';
                                if (userColors[name]) return userColors[name];
                                // simple hash to pick a color from palette
                                var h = 0;
                                for (var i = 0; i < name.length; i++) h = (h << 5) - h + name.charCodeAt(i);
                                var idx = Math.abs(h) % Chat.palette.length;
                                userColors[name] = Chat.palette[idx];
                                return userColors[name];
                            }

                            res.messages.forEach(function(m) {
                                var color = userColor(m.user || '');
                                var badge = '<span class="chat-user-badge" style="background:' + color + '"></span>';
                                var isCurrent = m.is_current || (typeof window.CHAT_CURRENT_USER !== 'undefined' && window.CHAT_CURRENT_USER === m.user);
                                var rowClass = (isCurrent ? 'table-primary' : '');
                                html += '<tr class="' + rowClass + '">' +
                                        '<td>' + badge + '<strong>' + escapeHtml(m.user) + '</strong></td>' +
                                        '<td>' + escapeHtml(m.content) + '</td>' +
                                        '<td class="text-center">' + escapeHtml(m.date) + '</td>' +
                                        '</tr>';
                            });
                        }
                        $('#chat-messages tbody').html(html);
                        var chatDiv = document.getElementById('chat-messages');
                        if (chatDiv) {
                            // Scroll to bottom so the latest messages are visible
                            chatDiv.scrollTop = chatDiv.scrollHeight;
                        }
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
