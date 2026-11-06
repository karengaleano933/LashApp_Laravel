# Explicación de mis Rutas de Autenticación y Seguridad (`routes/auth.php`)

En este archivo controlo todo el proceso de **ingreso, registro y seguridad** de mi aplicación **LashApp**. Es como la recepción principal de un edificio seguro: aquí decido por dónde entra una clienta nueva, cómo inicia sesión alguien que ya tiene cuenta, qué camino sigue una persona si olvidó su contraseña, y cómo se cierra la sesión de forma segura.

Tengo este archivo dividido en dos grandes salas:
1. **Rutas para Invitadas (`guest`)**: Puertas abiertas solo para quienes aún NO han iniciado sesión (como registrarse o poner su correo y clave).
2. **Rutas para Usuarias con Sesión Iniciada (`auth`)**: Puertas que solo se abren cuando la persona ya comprobó su identidad (como verificar el correo, cambiar la contraseña o salir del sistema).

---

## 1. Conexión de mis Ayudantes de Seguridad (Controladores de Autenticación)

Aquí llamo a los encargados especializados en cada tarea de seguridad:

| Código | ¿Qué hace? |
| :--- | :--- |
| `use App\Http\Controllers\Auth\AuthenticatedSessionController;` | Traigo al encargado de abrir la sesión (comprobar correo y contraseña) y de cerrarla (`logout`). |
| `use App\Http\Controllers\Auth\ConfirmablePasswordController;` | Traigo al ayudante que pide reconfirmar la contraseña antes de hacer alguna acción delicada. |
| `use App\Http\Controllers\Auth\EmailVerificationNotificationController;` | Traigo al encargado de reenviar el correo de verificación si a la persona no le llegó. |
| `use App\Http\Controllers\Auth\EmailVerificationPromptController;` | Traigo a quien muestra el aviso pidiendo que verifique su correo antes de continuar. |
| `use App\Http\Controllers\Auth\NewPasswordController;` | Traigo al encargado de guardar la nueva contraseña cuando alguien la restablece. |
| `use App\Http\Controllers\Auth\PasswordController;` | Traigo al ayudante que actualiza la clave secreta desde el panel de perfil. |
| `use App\Http\Controllers\Auth\PasswordResetLinkController;` | Traigo al encargado de generar y mandar el enlace de recuperación al correo de la usuaria. |
| `use App\Http\Controllers\Auth\RegisteredUserController;` | Traigo al ayudante que atiende a clientas nuevas, crea su cuenta y las guarda en el sistema. |
| `use App\Http\Controllers\Auth\VerifyEmailController;` | Traigo al ayudante que revisa el enlace que la persona tocó en su correo y da el visto bueno. |
| `use Illuminate\Support\Facades\Route;` | Traigo la herramienta básica de Laravel para crear los caminos y accesos web. |

---

## 2. Puertas para Invitadas y Nuevas Usuarias (`middleware('guest')`)

Este grupo tiene el candado `guest`: solo pueden entrar personas que **no** tengan una sesión abierta. Si alguien que ya está conectado intenta entrar aquí, el sistema la lleva directamente a su panel principal para no confundirla:

| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::middleware('guest')->group(function () { ... });` | Agrupo todas las rutas de registro y login bajo la regla de "solo para personas no identificadas todavía". |

### A. Registro de Nuevas Clientas
| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('register', [RegisteredUserController::class, 'create'])->name('register');` | Abre la pantalla con el formulario para que una nueva clienta escriba su nombre, correo, teléfono y elija su contraseña. |
| `Route::post('register', [RegisteredUserController::class, 'store']);` | Recibe los datos que escribió la clienta en el formulario, valida que no falte nada y crea su cuenta en el sistema. |

### B. Inicio de Sesión (`Login`)
| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');` | Abre la pantalla de acceso donde la usuaria coloca su correo electrónico y su contraseña. |
| `Route::post('login', [AuthenticatedSessionController::class, 'store']);` | Recibe el correo y la contraseña, comprueba en la base de datos si son correctos y le abre la puerta al sistema. |

### C. Recuperación de Contraseña Olvidada
| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');` | Abre la pantalla que dice *"¿Olvidaste tu contraseña?"* con una casilla para que la usuaria escriba su correo. |
| `Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');` | Recibe el correo de la persona, genera un enlace seguro de un solo uso y se lo envía a su bandeja de entrada. |
| `Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');` | Abre la pantalla a donde llega la persona cuando hace clic en el enlace de su correo, con una clave secreta temporal (`token`) para validar que sí es ella. |
| `Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');` | Recibe la nueva contraseña elegida por la persona, la actualiza en la base de datos y borra la contraseña anterior. |

---

## 3. Puertas para Usuarias Conectadas (`middleware('auth')`)

Este grupo está protegido por el candado `auth`: nadie puede entrar si no ha iniciado sesión previamente:

| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::middleware('auth')->group(function () { ... });` | Agrupo las rutas que exigen identidad confirmada para funcionar. |

### A. Verificación del Correo Electrónico
| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');` | Muestra una pantalla avisando: *"Por favor revisa tu correo y haz clic en el botón de confirmación para activar todas las funciones"*. |
| `Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)` | Es el camino que se activa cuando la persona hace clic en el botón de su correo electrónico. |
| `->middleware(['signed', 'throttle:6,1'])` | Candados extra: comprueba que el enlace no haya sido alterado (`signed`) y limita a un máximo de 6 intentos por minuto (`throttle`) para evitar ataques de personas malintencionadas. |
| `->name('verification.verify');` | Apodo interno para este camino de verificación. |
| `Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])` | Botón para volver a enviar el correo de confirmación si a la persona no le llegó o se le perdió. |
| `->middleware('throttle:6,1')->name('verification.send');` | Limita los reenvíos a 6 por minuto para no saturar los envíos de correo. |

### B. Confirmación y Actualización de Contraseña
| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');` | Muestra una pantalla pidiendo que vuelva a ingresar su clave actual antes de entrar a una zona muy delicada de la app. |
| `Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);` | Revisa si la contraseña que escribió para reconfirmar es correcta. |
| `Route::put('password', [PasswordController::class, 'update'])->name('password.update');` | Recibe la contraseña actual y la nueva que la usuaria configuró en su perfil para actualizarla. |

### C. Cierre de Sesión (`Logout`)
| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');` | Cierra la sesión de la persona, borra los permisos temporales del navegador por seguridad y la regresa a la pantalla principal del salón. |
