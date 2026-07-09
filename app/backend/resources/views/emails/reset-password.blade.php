<x-emails.shell title="Recuperar Contraseña - Ñapa">
  <!-- HERO LADO A LADO: TEXTO | IMAGEN -->
  <tr>
    <td bgcolor="#6B3A8F" style="background-color:#6B3A8F;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td width="290" valign="middle" bgcolor="#6B3A8F" style="background-color:#6B3A8F; padding:44px 36px;">
            <div style="text-align:center;">
              <img src="{{ \App\Support\EmailAsset::url('logo-napa-tight.png') }}" width="72" alt="Ñapa" style="display:inline-block; border:0; margin-bottom:-6px;">
              <div class="fnt" style="font-family:'Poppins', Arial, sans-serif; font-size:24px; font-weight:900; letter-spacing:5px; color:#ffffff; margin-bottom:22px; text-align:center;">ÑAPA</div>
            </div>
            <div class="fnt" style="font-family:'Poppins', Arial, sans-serif; font-size:33px; font-weight:900; line-height:1.08; letter-spacing:-1px; color:#ffffff; margin-bottom:12px;">Recibimos tu solicitud</div>
            <div class="fnt" style="font-family:'DM Sans', Arial, sans-serif; font-size:15px; line-height:1.55; color:#ffffff;">Te enviamos este enlace seguro. Caduca en {{ $expireMinutes }} minutos.</div>
          </td>
          <td width="310" valign="top" style="line-height:0; font-size:0;">
            <img src="{{ \App\Support\EmailAsset::url('aliado-reset-password-baked-v2.jpg') }}" width="310" alt="Candado grabado con la palabra contraseña y el logo Ñapa" style="display:block; width:100%; max-width:310px; height:auto; border:0;">
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
          <td class="fnt" style="font-family:'Poppins', Arial, sans-serif; font-size:11px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; color:#4B236A;">Seguridad</td>
        </tr>
        <tr><td style="height:16px; line-height:16px; font-size:0;">&nbsp;</td></tr>
        <tr>
          <td class="fnt" style="font-family:'DM Sans', Arial, sans-serif; font-size:15px; line-height:1.7; color:#333333;">
            <p style="margin:0 0 16px 0;">¡Hola!</p>
            <p style="margin:0;">Recibiste este correo porque se solicitó un restablecimiento de contraseña para tu cuenta.</p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:28px 0 24px 0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td bgcolor="#DDE8BB" style="background-color:#DDE8BB; border-radius:6px;">
                  <a href="{{ $url }}" class="fnt" style="display:inline-block; padding:14px 34px; font-family:'Poppins', Arial, sans-serif; font-size:15px; font-weight:700; color:#4B236A; text-decoration:none;">Restablecer contraseña</a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="fnt" style="border-top:1px solid #eeeeee; padding-top:16px; font-family:'DM Sans', Arial, sans-serif; font-size:12px; line-height:1.6; color:#999999;">
            Si no solicitaste el restablecimiento, ignora este correo.<br><br>
            Saludos,<br>Tu amigo Ñapa
          </td>
        </tr>
      </table>
    </td>
  </tr>
</x-emails.shell>
