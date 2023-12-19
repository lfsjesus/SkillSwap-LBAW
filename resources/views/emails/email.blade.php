<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; background-color: #f7f7f7; padding: 20px;">
    <div style="max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <header style="border-bottom: 1px solid #eeeeee; padding-bottom: 20px; margin-bottom: 20px;">
            <h1 style="color: #333;">SkillSwap</h1>
        </header>

        <section style="margin-bottom: 20px;">
            <h2 style="color: #333;">Password Reset Request</h2>
            <p>Hello {{ $data['name'] }},</p>
            <p>You are receiving this email because we received a password reset request for your account.</p>
        </section>

        <section style="margin-bottom: 20px;">
            <a href="{{ url('/password/reset/'.$data['token']) }}" style="background-color: #8d73eb; color: white; padding: 10px 20px; text-align: center; text-decoration: none; border-radius: 5px; display: inline-block;">Reset Password</a>

        </section>


        <footer style="border-top: 1px solid #eeeeee; padding-top: 20px;">
            <p>If you did not request a password reset, please ignore this email or contact support if you have any questions.</p>
        </footer>

        <section style="text-align: center; margin-top: 30px;">
            <p>Thank you for being a part of SkillSwap!</p>
            <p><small>© SkillSwap, All rights reserved.</small></p>
        </section>
    </div>
</body>
</html>
