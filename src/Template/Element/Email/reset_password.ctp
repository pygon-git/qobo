<!-- Body content -->
<tr>
    <td class="content-cell">
        <h1><?= __("Hi {0}", $this->HtmlEmail->getRecepientName()); ?>,</h1>
        <p>You recently requested to reset your password for your <?= $this->SystemInfo->getProjectName() ?> account. Use the button below to reset it. <strong>This password reset is only valid for the next 24 hours.</strong></p>
        <!-- Action -->
        <table class="body-action" align="center" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center">
                <!-- Border based button https://litmus.com/blog/a-guide-to-bulletproof-buttons-in-email-design -->
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="center">
                                <table border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td>
                                            <a href="<?= $this->Url->build($activationUrl); ?>" class="button button--green" target="_blank">Reset your password</a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <p class="sub">If you’re having trouble with the button above, copy and paste the URL below into your web browser.</p>
        <p class="sub"><?= $this->Url->build($activationUrl); ?></p>
    </td>
</tr>

