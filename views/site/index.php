<?php

/* @var $this yii\web\View */

$this->title = 'Сервис коротких ссылок';
?>
<div class="site-index">
    <div class="jumbotron text-center bg-transparent">
        <h1 class="display-4">Сервис коротких ссылок</h1>
        <p class="lead">Создавайте короткие ссылки и QR коды для любых URL</p>
    </div>

    <div class="body-content">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <form id="shortLinkForm">
                            <div class="input-group mb-3">
                                <input type="url" class="form-control" id="urlInput" 
                                       placeholder="Введите URL (например: https://www.google.com)" 
                                       required>
                                <button class="btn btn-primary" type="submit" id="submitBtn">
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    ОК
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="resultContainer" class="mt-4" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Результат</h5>
                        </div>
                        <div class="card-body">
                            <div id="errorMessage" class="alert alert-danger" style="display: none;"></div>
                            <div id="successResult" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Короткая ссылка:</h6>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="shortUrlOutput" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('shortUrlOutput')">
                                                <i class="bi bi-clipboard"></i> Копировать
                                            </button>
                                        </div>
                                        <p class="text-muted small">
                                            <strong>Переходов:</strong> <span id="clicksCount">0</span>
                                        </p>
                                    </div>
                                    <div class="col-md-6 text-center">
                                        <h6>QR код:</h6>
                                        <div id="qrCodeContainer">
                                            <img id="qrCodeImage" class="img-fluid" style="max-width: 200px; max-height: 200px;">
                                        </div>
                                        <button class="btn btn-outline-primary btn-sm mt-2" onclick="downloadQR()">
                                            <i class="bi bi-download"></i> Скачать QR код
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$createUrl = \yii\helpers\Url::to(['site/create-short-link']);
$csrfToken = Yii::$app->request->csrfToken;

$js = <<<JS
$(document).ready(function() {
    $('#shortLinkForm').on('submit', function(e) {
        e.preventDefault();
        
        const url = $('#urlInput').val().trim();
        if (!url) {
            showError('Пожалуйста, введите URL');
            return;
        }
        
        // Показываем спиннер
        const submitBtn = $('#submitBtn');
        const spinner = submitBtn.find('.spinner-border');
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        // Скрываем предыдущие результаты
        $('#resultContainer').hide();
        $('#errorMessage').hide();
        $('#successResult').hide();
        
        $.ajax({
            url: '$createUrl',
            type: 'POST',
            data: {
                url: url,
                _csrf: '$csrfToken'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess(response);
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
                showError('Произошла ошибка при обработке запроса: ' + error);
            },
            complete: function() {
                // Скрываем спиннер
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });
});

function showError(message) {
    $('#errorMessage').text(message).show();
    $('#resultContainer').show();
}

function showSuccess(response) {
    $('#shortUrlOutput').val(response.shortUrl);
    $('#clicksCount').text(response.clicksCount);
    
    if (response.qrCode) {
        $('#qrCodeImage').attr('src', response.qrCode);
        $('#qrCodeContainer').show();
    } else {
        $('#qrCodeContainer').html('<p class="text-muted">QR код не был сгенерирован</p>');
    }
    
    $('#successResult').show();
    $('#resultContainer').show();
}

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999); // Для мобильных устройств
    
    try {
        document.execCommand('copy');
        showToast('Ссылка скопирована в буфер обмена');
    } catch (err) {
        showToast('Ошибка при копировании');
    }
}

function downloadQR() {
    const qrImage = document.getElementById('qrCodeImage');
    const link = document.createElement('a');
    link.download = 'qr-code.svg';
    link.href = qrImage.src;
    link.click();
}

function showToast(message) {
    // Простое уведомление
    const toast = $('<div class="toast position-fixed" style="top: 20px; right: 20px; z-index: 1050;">' +
        '<div class="toast-body">' + message + '</div>' +
        '</div>');
    
    $('body').append(toast);
    toast.toast({delay: 3000}).toast('show');
    
    setTimeout(function() {
        toast.remove();
    }, 3000);
}
JS;

$this->registerJs($js);
?>
