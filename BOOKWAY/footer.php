</div>
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-content">
            <p class="copyright">&copy; <?= date('Y') ?>, BookWay</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Обработка кликов по кнопкам статусов
            document.querySelectorAll('.status-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    if (this.dataset.currentStatus === 'null') {
                        updateBookStatus(this.dataset.bookId, 'planned');
                    }
                    e.stopPropagation();
                });
            });

            // Обработка выбора статуса из dropdown
            document.querySelectorAll('.status-option').forEach(option => {
                option.addEventListener('click', function() {
                    updateBookStatus(this.dataset.bookId, this.dataset.status);
                });
            });
        });

        function updateBookStatus(bookId, status) {
            fetch('update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ book_id: bookId, status }),
                credentials: 'same-origin'
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Ошибка сервера');
                }
                return data;
            })
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    showToast(data.message || 'Статус обновлен, но получен неожиданный ответ');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast(error.message || 'Ошибка сети');
            });
        }

        // Вспомогательная функция для уведомлений
        function showToast(message, type = 'error') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 5000);
        }
    </script>
</footer>