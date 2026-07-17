<x-emails.shell title="Comercio Rechazado - Ñapa">
  <!-- HERO LADO A LADO: TEXTO | IMAGEN -->
  <tr>
    <td bgcolor="#2A1545" style="background-color:#2A1545;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td width="290" valign="middle" bgcolor="#2A1545" style="background-color:#2A1545; padding:44px 36px;">
            <div style="text-align:center;">
              <img src="{{ \App\Support\EmailAsset::url('logo-napa-tight.png') }}" width="72" alt="Ñapa" style="display:inline-block; border:0; margin-bottom:-6px;">
              <div class="fnt" style="font-family:'Poppins', Arial, sans-serif; font-size:24px; font-weight:900; letter-spacing:5px; color:#ffffff; margin-bottom:22px; text-align:center;">ÑAPA</div>
            </div>
            <div class="fnt" style="font-family:'Poppins', Arial, sans-serif; font-size:33px; font-weight:900; line-height:1.08; letter-spacing:-1px; color:#ffffff; margin-bottom:12px;">Comercio rechazado</div>
            <div class="fnt" style="font-family:'DM Sans', Arial, sans-serif; font-size:15px; line-height:1.55; color:#ffffff;">Necesitamos algunos datos más para verificar tu cuenta.</div>
          </td>
          <td width="310" valign="top" style="line-height:0; font-size:0;">
            <img src="{{ \App\Support\EmailAsset::url('aliado-revision-pendiente-baked.jpg') }}" width="310" alt="Carpeta de ajustes en proceso sobre un escritorio" style="display:block; width:100%; max-width:310px; height:auto; border:0;">
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
          <td class="fnt" style="font-family:'Poppins', Arial, sans-serif; font-size:11px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; color:#4B236A;">Acción requerida</td>
        </tr>
        <tr><td style="height:16px; line-height:16px; font-size:0;">&nbsp;</td></tr>
        <tr>
          <td class="fnt" style="font-family:'DM Sans', Arial, sans-serif; font-size:15px; line-height:1.7; color:#333333;">
            <p style="margin:0 0 16px 0; font-weight:600;">Hola {{ $notifiable->name ?? 'Usuario' }},</p>
            <p style="margin:0 0 16px 0;">Lamentamos informarte que tu comercio ha sido rechazado tras el proceso de verificación.</p>
            <p style="margin:0 0 16px 0;">El motivo del rechazo es:</p>
            <p style="margin:0 0 16px 0; color:#666666; font-style:italic;">{{ $customMessage }}</p>
            <p style="margin:0 0 16px 0;">Gracias por tu interés en nuestra plataforma.</p>
            <p style="margin:0;">Saludos,<br>Tu amigo Ñapa<br>Email: soporte@napaapp.com.co</p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:28px 0 4px 0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td bgcolor="#4B236A" style="background-color:#4B236A; border-radius:6px;">
                  <a href="{{ $ctaUrl }}" class="fnt" style="display:inline-block; padding:14px 34px; font-family:'Poppins', Arial, sans-serif; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none;">Completar registro</a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</x-emails.shell>
