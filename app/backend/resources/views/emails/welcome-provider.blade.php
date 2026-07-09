<x-emails.shell title="Bienvenida Aliado - Ñapa">
  <!-- HERO LADO A LADO: TEXTO | IMAGEN -->
  <tr>
    <td bgcolor="#4B236A" style="background-color:#4B236A;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td width="290" valign="middle" bgcolor="#4B236A" style="background-color:#4B236A; padding:44px 36px;">
            <div style="text-align:center;">
              <img src="{{ \App\Support\EmailAsset::url('logo-napa-tight.png') }}" width="72" alt="Ñapa" style="display:inline-block; border:0; margin-bottom:-6px;">
              <div class="fnt" style="font-family:'Poppins', Arial, sans-serif; font-size:24px; font-weight:900; letter-spacing:5px; color:#ffffff; margin-bottom:22px; text-align:center;">ÑAPA</div>
            </div>
            <div class="fnt" style="font-family:'Poppins', Arial, sans-serif; font-size:38px; font-weight:900; line-height:1.08; letter-spacing:-1px; color:#ffffff; margin-bottom:12px;">¡Bienvenido!</div>
            <div class="fnt" style="font-family:'DM Sans', Arial, sans-serif; font-size:15px; line-height:1.55; color:#ffffff;">Qué gran noticia contar contigo como <b>Aliado</b>.</div>
          </td>
          <td width="310" valign="top" style="line-height:0; font-size:0;">
            <img src="{{ \App\Support\EmailAsset::url('aliado-handshake-baked.jpg') }}" width="310" alt="Aliado y representante Ñapa cerrando el acuerdo con un apretón de manos" style="display:block; width:100%; max-width:310px; height:auto; border:0;">
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- CUERPO -->
  <tr>
    <td bgcolor="#ffffff" style="background-color:#ffffff; padding:40px 40px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td class="fnt" style="font-family:'DM Sans', Arial, sans-serif; font-size:15px; line-height:1.7; color:#333333;">
            <p style="margin:0 0 16px 0; font-weight:600;">Hola {{ $notifiable->name }},</p>
            <p style="margin:0 0 16px 0;">Tu registro ha sido exitoso.</p>
            <p style="margin:0 0 16px 0;">Ya puedes acceder a tu cuenta y comenzar a utilizar nuestros servicios.</p>
            <p style="margin:0 0 16px 0;">¡Gracias por confiar en nosotros!</p>
            <p style="margin:0;">Saludos,<br>Tu amigo Ñapa</p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:28px 0 24px 0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td bgcolor="#DDE8BB" style="background-color:#DDE8BB; border-radius:6px;">
                  <a href="{{ $ctaUrl }}" class="fnt" style="display:inline-block; padding:14px 34px; font-family:'Poppins', Arial, sans-serif; font-size:15px; font-weight:700; color:#4B236A; text-decoration:none;">Ir a mi cuenta</a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="fnt" style="border-top:1px solid #eeeeee; padding-top:16px; font-family:'DM Sans', Arial, sans-serif; font-size:12px; color:#999999;">Si tienes dudas, escribe a soporte@napaapp.com.co</td>
        </tr>
      </table>
    </td>
  </tr>
</x-emails.shell>
