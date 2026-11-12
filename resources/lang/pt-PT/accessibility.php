<?php

// European Portuguese (Portugal), the 'pt-PT' locale.
//
// It starts from resources/lang/pt (Brazilian Portuguese) and replaces every string Portugal says
// differently: bilhete for ingresso, ecrã, telemóvel, utilizador, palavra-passe, definições,
// "a carregar" for "carregando", and the formal third person without "você". A key that is the
// same in both is not repeated here. A key added to pt and not here reaches pt-PT as pt has it, so
// tests/Feature/EuropeanPortugueseTest.php fails when it carries a Brazilian word: add the
// European form below.
return array_replace(require __DIR__.'/../pt/accessibility.php', [

]);
