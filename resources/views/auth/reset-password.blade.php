<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Reset Password</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.bunny.net">  
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.0/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <link href="https://prim.o513.dev/public/css/app.css" rel="stylesheet">
    <link href="https://prim.o513.dev/public/css/style.css?v=9" rel="stylesheet">
    <link href="https://prim.o513.dev/public/css/speech-activated.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.14.0/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment-with-locales.min.js" integrity="sha512-4F1cxYdMiAW98oomSLaygEwmCnIP38pb4Kx70yQYqRwLVCs3DbRumfBq82T08g/4LJ/smbFGFpmeFlQgoDccgg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }
    function getCookie(name) {
        const nameEQ = name + "=";
        const cookies = document.cookie.split(';');
        
        for(let i = 0; i < cookies.length; i++) {
            let cookie = cookies[i];
            
            // Remove leading spaces if any
            while (cookie.charAt(0) === ' ') {
                cookie = cookie.substring(1, cookie.length);
            }
            
            // Check if the cookie matches the desired name
            if (cookie.indexOf(nameEQ) === 0) {
                return cookie.substring(nameEQ.length, cookie.length);
            }
        }
        
        return null;  // Return null if cookie is not found
    }
    /**
    * Sweet alert confirmation template
    * @returns {any}
    */
    function sweatAlertConfirmation(messageText,messageIcon,messageBtnText,messageBtnStyle,messageCancelStyle) {
        return Swal.fire({
            text: `${messageText}`,
            icon: `${messageIcon}`,
            buttonsStyling: false,
            showCancelButton: true,
            confirmButtonText: `${messageBtnText}`,
            focusConfirm: true,
            cancelButtonText: `<span class="mdi mdi-cancel"></span> Cancel`,
            customClass: {
                confirmButton: `${messageBtnStyle}`,
                cancelButton: `${messageCancelStyle}`,
            },
        });
    }

    /**
    * The function `sweetAlertStatusMessage` displays a SweetAlert popup with a custom message and
    * icon.
    * @param {any} messageText
    * @param {any} messageIcon
    * @returns {any}
    */
    function sweetAlertStatusMessage(messageText, messageIcon) {
        return Swal.fire({
            text: `${messageText}`,
            icon: `${messageIcon}`,
            heightAuto: false,
            buttonsStyling: false,
            customClass: {
                confirmButton: "btn btn-primary me-2",
            },
            confirmButtonText: `Okay`,
        });
    }
    $(document).ready(function() {
        $('body').on('show.bs.modal', '.modal', function () { // when the modal begins opening
            $('.blur-overlay').show();
            $('.triangle-left').animate({
                top: 0,
                left: 0
            }, 250, 'linear');
            $('.triangle-right').animate({
                bottom: 0,
                right: 0
            }, 250, 'linear');
        });

        $('body').on('shown.bs.modal', '.modal', function () { // after the animation of opening ends
            const _this = this;
            const input = $(_this).find('input').first();

            if (input.length) {
                input.focus();
            }
        });

        $('body').on('hide.bs.modal', '.modal', function (e) { // when the modal begins closing
            $('.blur-overlay').hide();
            $('.triangle-left').animate({
                top: -62.5,
                left: -62.5
            }, 125, 'linear');
            $('.triangle-right').animate({
                bottom: -62.5,
                right: -62.5
            }, 125, 'linear');
        });

        $('body').on('click', '.switchable-tabs', function() {
            let group = $(this).data('group');
            $('.switchable-tabs').addClass('tewi-tab-hoverable');
            $('.switchable-tabs').removeClass('tewi-tab-active');
            $(this).addClass('tewi-tab-active');
            $(this).removeClass('tewi-tab-hoverable');
            $('.container-groups').hide();
            $('.container-groups[data-group=' + group + ']').show();
        });

        const svgIcons = {
            main: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z" />
                        <path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z" />
                    </svg>`,
            settings: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path fill-rule="evenodd" d="M11.078 2.25c-.917 0-1.699.663-1.85 1.567L9.05 4.889c-.02.12-.115.26-.297.348a7.493 7.493 0 0 0-.986.57c-.166.115-.334.126-.45.083L6.3 5.508a1.875 1.875 0 0 0-2.282.819l-.922 1.597a1.875 1.875 0 0 0 .432 2.385l.84.692c.095.078.17.229.154.43a7.598 7.598 0 0 0 0 1.139c.015.2-.059.352-.153.43l-.841.692a1.875 1.875 0 0 0-.432 2.385l.922 1.597a1.875 1.875 0 0 0 2.282.818l1.019-.382c.115-.043.283-.031.45.082.312.214.641.405.985.57.182.088.277.228.297.35l.178 1.071c.151.904.933 1.567 1.85 1.567h1.844c.916 0 1.699-.663 1.85-1.567l.178-1.072c.02-.12.114-.26.297-.349.344-.165.673-.356.985-.57.167-.114.335-.125.45-.082l1.02.382a1.875 1.875 0 0 0 2.28-.819l.923-1.597a1.875 1.875 0 0 0-.432-2.385l-.84-.692c-.095-.078-.17-.229-.154-.43a7.614 7.614 0 0 0 0-1.139c-.016-.2.059-.352.153-.43l.84-.692c.708-.582.891-1.59.433-2.385l-.922-1.597a1.875 1.875 0 0 0-2.282-.818l-1.02.382c-.114.043-.282.031-.449-.083a7.49 7.49 0 0 0-.985-.57c-.183-.087-.277-.227-.297-.348l-.179-1.072a1.875 1.875 0 0 0-1.85-1.567h-1.843ZM12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" clip-rule="evenodd" />
                    </svg>`,
            back: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>`
        }

        $('body').on('click', '.group-navigate-btn', function() {
            const $button = $(this);
            const group = $button.data('group');

            let groupBack = $button.data('group_back') ?? 'main';
            if (groupBack == 'main' && group == 'main') {
                groupBack = 'settings';
            }

            const $current = $('.group-container:visible');
            const $next = $(`.group-container[data-group="${group}"]`);

            $current.addClass(group === 'main' ? 'animate__animated animate__fadeOutRight' : 'animate__animated animate__fadeOutLeft');

            $current.one('animationend', function() {
                $current.hide().removeClass('animate__animated animate__fadeOutRight animate__fadeOutLeft');
                $next.show().addClass(group === 'main' ? 'animate__animated animate__fadeInLeft' : 'animate__animated animate__fadeInRight');
                $next.one('animationend', function() {
                    $next.removeClass('animate__animated animate__fadeInLeft animate__fadeInRight');
                });
            });
            
            $('.group-container').removeClass('animate__durationExcluded');

            $('.group-navigate-btn-back').data('group', groupBack).html(svgIcons[groupBack]);
        });

        $('body').on('click', '.btn', function(e) {
            const $btn = $(this);
            const ripple = $('<span class="ripple"></span>');
            
            const offset = $btn.offset();
            const x = e.pageX - offset.left;
            const y = e.pageY - offset.top;
            const diameter = Math.max($btn.outerWidth(), $btn.outerHeight());
            const radius = diameter / 2;

            ripple.css({
                width: diameter,
                height: diameter,
                left: x - radius,
                top: y - radius
            });

            $btn.append(ripple);
            
            ripple.on('animationend webkitAnimationEnd', function() {
                ripple.remove();
            });
        });

        $('body').on('click', '.unavailable-btn', function() {
            let messageText = 'Feature not yet implemented!';
            let messageIcon = 'warning';
            sweetAlertStatusMessage(messageText, messageIcon);
        });
    });
    </script>
</head>
<body class="p-5">
    <h2 class="text-gradient-primary">Reset Password</h2>

    @if (session('status'))
        <p style="color: green;">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ request('email') }}">

        <div>
            <label for="password">New Password</label><br>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <div>
            <label for="password_confirmation">Confirm Password</label><br>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </div>
    </form>
</body>
</html>