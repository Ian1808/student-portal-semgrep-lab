<?php
// VULNERABILITY: Information disclosure (ASVS 7.2.1)
// Exposes PHP configuration, server paths, modules

phpinfo();
?>