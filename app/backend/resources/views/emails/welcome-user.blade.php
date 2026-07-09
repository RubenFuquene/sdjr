<x-emails.shell title="Bienvenida Usuario - Ñapa">
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
            <div class="fnt" style="font-family:'Poppins', Arial, sans-serif; font-size:38px; font-weight:900; line-height:1.08; letter-spacing:-1px; color:#4B236A; margin-bottom:12px;">¡Bienvenido!</div>
            <div class="fnt" style="font-family:'DM Sans', Arial, sans-serif; font-size:15px; line-height:1.55; color:#4B236A;">Gracias por registrarte en nuestra aplicación, estamos felices de tenerte con nosotros.</div>
          </td>
          <td width="310" valign="middle" bgcolor="#DDE8BB" style="background-color:#DDE8BB; line-height:0; font-size:0;">
            <img src="{{ \App\Support\EmailAsset::url('usuario-bienvenida-baked.jpg') }}" width="310" alt="Bolsa de compras Ñapa con hojas, representando productos recuperados" style="display:block; width:100%; max-width:310px; height:auto; border:0;">
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
            <p style="margin:0 0 16px 0;">Tu registro ha sido exitoso.</p>
            <p style="margin:0 0 16px 0;">Ya puedes acceder a tu cuenta y comenzar a utilizar nuestros servicios.</p>
            <p style="margin:0 0 16px 0;">¡Gracias por confiar en nosotros!</p>
            <p style="margin:0;">Saludos,<br>Tu amigo Ñapa</p>
          </td>
        </tr>
        <tr><td style="height:24px; line-height:24px; font-size:0;">&nbsp;</td></tr>
        <tr>
          <td bgcolor="#F3F6E8" style="background-color:#F3F6E8; border-left:4px solid #5C7A38; border-radius:4px; padding:16px 18px;">
            <p class="fnt" style="margin:0 0 4px 0; font-family:'DM Sans', Arial, sans-serif; font-size:13px; font-weight:600; color:#333333;">🌱 Tu viaje hacia un estilo de vida más sostenible</p>
            <p class="fnt" style="margin:0; font-family:'DM Sans', Arial, sans-serif; font-size:12px; color:#666666;">Explora, descubre y elige mejor para ti y para el planeta.</p>
          </td>
        </tr>
        <tr>
          <td align="center" style="padding:28px 0 4px 0;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td bgcolor="#4B236A" style="background-color:#4B236A; border-radius:6px;">
                  <a href="{{ $ctaUrl }}" class="fnt" style="display:inline-block; padding:14px 34px; font-family:'Poppins', Arial, sans-serif; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none;">Explorar ofertas</a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</x-emails.shell>
