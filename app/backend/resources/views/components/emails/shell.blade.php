@props(['title'])
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $title }}</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800;900&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<!--[if mso]><style type="text/css">table {border-collapse:collapse;} .fnt {font-family:Arial, sans-serif !important;}</style><![endif]-->
</head>
<body style="margin:0; padding:0; background-color:#f0f0f0;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f0f0;">
    <tr>
      <td align="center" style="padding:24px 12px;">

        <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:600px; background-color:#ffffff;">

          {{ $slot }}

        </table>

      </td>
    </tr>
  </table>
</body>
</html>
