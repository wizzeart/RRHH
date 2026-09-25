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
        isAdmin: false,

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
                var receiver = $('#mensaje-texto').attr('data-receiver') || '';
                var originalMessageToSend = $('#mensaje-texto').attr('data-original-message') || '';
                var originalUserToSend = $('#mensaje-texto').attr('data-receiver') || '';
                $('#mensaje-texto').prop('disabled', true);
                $.ajax({
                    url: 'api-app.php',
                    type: 'POST',
                    data: { module: 'chat', method: 'sendMessage', content: message, receiver_user: receiver, original_message: originalMessageToSend, original_user: originalUserToSend },
                    dataType: 'json',
                    success: function(res) {
                        if (res && res.status === 1) {
                            // Obtener el destinatario antes de resetear
                            var receiver = $('#mensaje-texto').attr('data-receiver');
                            var message = $('#mensaje-texto').val().trim();
                            var originalMessage = $('#mensaje-texto').attr('data-original-message');
                            
                            $('#mensaje-texto').val('');
                            // Reset reply target and placeholder
                                        $('#mensaje-texto')
                                           .removeAttr('data-receiver')
                                           .removeAttr('data-original-message')
                                           .attr('placeholder', 'Escribe tu mensaje...');
                            
                            // Notificar al destinatario específico
                            if (receiver) {
                                Chat.notificacion(
                                    "🔔 Respuesta de " + (window.CHAT_CURRENT_USER || 'Admin'),
                                    "Para: " + receiver + "\n" +
                                    "Tu mensaje: " + message.substring(0, 150) + (message.length > 150 ? '...' : '')
                                );
                            } else {
                                // Notificar al enviar un mensaje sin responder
                                Chat.notificacion(
                                    "✅ Tu mensaje fue enviado",
                                    message.substring(0, 150) + (message.length > 150 ? '...' : '')
                                );
                            }
                            
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

            // Delegated: click on reply buttons (admin only)
            $(document).on('click', '.reply-btn', function() {
                var user = $(this).data('user');
                var originalMessage = $(this).data('original-message') || '';
                if (!user) return;
                $('#mensaje-texto')
                    .attr('data-receiver', user)
                    .attr('data-original-message', originalMessage)
                    .attr('placeholder', 'Responder a ' + user + '...')
                    .focus();
            });

            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    if (Chat.updateTimer) clearInterval(Chat.updateTimer);
                } else {
                    Chat.startUpdates();
                }
            });
        },

        lastMessageTimestamp: null,

        notificacion: async function(title, message, icon = 'img/manifest-icons/icon-192x192.png', options = {}) {
            try {
                const permiso = await Notification.requestPermission();
                if (permiso !== "granted") {
                    console.log("Permiso para notificaciones denegado.");
                    return;
                }

                // Opciones avanzadas para notificaciones
                const notificationOptions = {
                    body: message,
                    icon: icon,
                    badge: icon,
                    requireInteraction: true,
                    silent: false,
                    tag: 'chat-notification',
                    renotify: true,
                    data: {
                        url: window.location.href
                    },
                    ...options
                };

                if ("serviceWorker" in navigator) {
                    await navigator.serviceWorker.ready;
                    navigator.serviceWorker.controller?.postMessage({
                        type: "SHOW_NOTIFICATION",
                        payload: {
                            title: title,
                            ...notificationOptions
                        },
                    });
                } else {
                    const noti = new Notification(title, notificationOptions);
                    noti.onclick = () => {
                        window.focus();
                        noti.close();
                    };
                }

                // Vibración táctil
                if (navigator.vibrate) {
                    navigator.vibrate([200, 100, 200]);
                }

                // Reproducir sonido de notificación si está disponible
                try {
                    const audio = new Audio('data:audio/wav;base64,UklGRiYAAABXQVZFZm10IBAAAAABAAEAQB8AAAB9AAACABAAZGF0YQIAAAAAAA==');
                    audio.play().catch(e => console.log('No se pudo reproducir sonido:', e));
                } catch (e) {
                    console.log('Audio no disponible');
                }
            } catch (err) {
                console.error("Error mostrando la notificación:", err);
            }
        },

        loadMessages: function() {
            var self = this;
            $.ajax({
                url: 'api-app.php',
                type: 'GET',
                data: { module: 'chat', method: 'getChatMessages' },
                dataType: 'json',
                success: function(res) {
                    if (res && res.status === 1 && Array.isArray(res.messages)) {
                        // Verificar nuevos mensajes
                        if (res.messages.length > 0) {
                            const lastMessage = res.messages[res.messages.length - 1];
                            const timestamp = new Date(lastMessage.date).getTime();
                            
                            // Si hay un mensaje más reciente y la ventana está minimizada
                            if ((!self.lastMessageTimestamp || timestamp > self.lastMessageTimestamp) && 
                                document.hidden && 
                                !lastMessage.is_current) {
                                const senderRole = lastMessage.sender_role === 'admin' ? '👨‍💼 Admin' : '👤 Trabajador';
                                self.notificacion(
                                    "💬 Nuevo mensaje de " + escapeHtml(lastMessage.user) + " (" + senderRole + ")",
                                    escapeHtml(lastMessage.content),
                                    'img/manifest-icons/icon-192x192.png',
                                    {
                                        tag: 'chat-notification-' + lastMessage.user,
                                        renotify: true
                                    }
                                );
                            }
                            self.lastMessageTimestamp = timestamp;
                        }
                        Chat.isAdmin = !!res.is_admin;
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
                                var role = (m.sender_role === 'admin') ? '<span class="label label-info" style="margin-right:6px;">Admin</span>' : '<span class="label label-default" style="margin-right:6px;">Trabajador</span>';
                                var replyBtn = '';
                                if (Chat.isAdmin && m.sender_role === 'worker' && m.user) {
                                    replyBtn = ' <button type="button" class="btn btn-xs btn-primary reply-btn" data-user="' + escapeHtml(m.user) + '" data-original-message="' + escapeHtml(m.content) + '">Responder</button>';
                                }

                                // Render reply context if present
                                var replyHtml = '';
                                if (m.reply_to_content && m.reply_to_content.toString().trim() !== '') {
                                    var replyAuthor = m.reply_to_user ? escapeHtml(m.reply_to_user) : '';
                                    replyHtml = '<div class="chat-reply" style="border-left:3px solid #e0e0e0;padding-left:8px;margin-bottom:6px;color:#555;font-size:12px;">'
                                              + (replyAuthor ? '<strong>' + replyAuthor + '</strong>: ' : '')
                                              + escapeHtml(m.reply_to_content)
                                              + '</div>';
                                }

                                html += '<tr class="' + rowClass + '">' +
                                        '<td>' + badge + '<strong>' + escapeHtml(m.user) + '</strong></td>' +
                                        '<td>' + role + replyHtml + escapeHtml(m.content) + replyBtn + '</td>' +
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
