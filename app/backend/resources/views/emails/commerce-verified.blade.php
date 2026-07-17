<x-emails.shell title="Registro Exitoso - Ñapa">
  <!-- HERO LADO A LADO: TEXTO | IMAGEN -->
  <tr>
    <td bgcolor="#DDE8BB" style="background-color:#DDE8BB;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td width="290" valign="middle" bgcolor="#DDE8BB" style="background-color:#DDE8BB; padding:44px 36px;">
            <div style="text-align:center;">
              <img src="{{ \App\Support\EmailAsset::url('logo-napa-tight.png') }}" width="72" alt="Ñapa" style="display:inline-block; border:0; margin-bottom:-6px;">
              <div class="fnt" style="font-family:'Poppins', Arial, sans-serif; font-size:24px; font-weight:900; letter-spacing:5px; color:#4B236A; margin-bottom:22px; text-align:center;">ÑAPA</div>
            </div>
            <div class="fnt" style="font-family:'Poppins', Arial, sans-serif; font-size:31px; font-weight:900; line-height:1.08; letter-spacing:-1px; color:#4B236A;">¡Tu comercio ha sido verificado!</div>
          </td>
          <td width="310" valign="top" style="line-height:0; font-size:0;">
            <img src="{{ \App\Support\EmailAsset::url('aliado-registro-exitoso-baked.jpg') }}" width="310" alt="Agente Ñapa confirmando el registro exitoso del aliado con el pulgar arriba" style="display:block; width:100%; max-width:310px; height:auto; border:0;">
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
            <p style="margin:0 0 16px 0;">Hola {{ $notifiable->name }},</p>
            <p style="margin:0 0 16px 0;">Nos complace informarte que tu comercio ha sido verificado y ahora se encuentra activo en la plataforma.</p>
            @if($customMessage)
            <p style="margin:0 0 16px 0;">{{ $customMessage }}</p>
            @endif
            <p style="margin:0 0 16px 0;">Ya puedes acceder a todas las funcionalidades disponibles para comercios verificados.</p>
            <p style="margin:0;">Saludos,<br>Tu amigo Ñapa</p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:28px 0 4px 0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td bgcolor="#4B236A" style="background-color:#4B236A; border-radius:6px;">
                  <a href="{{ $commerceUrl }}" class="fnt" style="display:inline-block; padding:14px 34px; font-family:'Poppins', Arial, sans-serif; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none;">Acceder a tu comercio</a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</x-emails.shell>
