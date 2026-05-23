<?php

use Flex\Core\Routing\View;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Flex CMS') ?></title>
</head>

<body
    style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0"
        style="background-color: #f8fafc; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="100%"
                    style="max-width: 600px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; border-collapse: collapse;">
                    <tr>
                        <td style="background-color: #0f172a; padding: 24px; text-align: center;">
                            <h1
                                style="margin: 0; color: #ffffff; font-size: 20px; font-weight: 800; letter-spacing: 0.5px;">
                                Flex CMS
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 32px; color: #334155; font-size: 16px; line-height: 1.6;">
                            <?php View::component($subComponent, $subData ?? [], 'components/emails'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="background-color: #f1f5f9; padding: 16px; text-align: center; color: #64748b; font-size: 12px;">
                            &copy; <?= date('Y') ?> Flex CMS. Всички права запазени.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
