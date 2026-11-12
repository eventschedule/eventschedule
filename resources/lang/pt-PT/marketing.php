<?php

// European Portuguese (Portugal), the 'pt-PT' locale.
//
// It starts from resources/lang/pt (Brazilian Portuguese) and replaces every string Portugal says
// differently: bilhete for ingresso, ecrã, telemóvel, utilizador, palavra-passe, definições,
// "a carregar" for "carregando", and the formal third person without "você". A key that is the
// same in both is not repeated here. A key added to pt and not here reaches pt-PT as pt has it, so
// tests/Feature/EuropeanPortugueseTest.php fails when it carries a Brazilian word: add the
// European form below.
return array_replace(require __DIR__.'/../pt/marketing.php', [
    'home_title' => 'Event Schedule - Calendário de eventos, bilhetes e marcações, grátis',
    'home_description' => 'Publique os seus eventos numa página de calendário, receba inscrições gratuitas sem limite e marcações. Venda bilhetes no Pro, sem taxa de plataforma.',
    'pricing_description' => 'Comece grátis: eventos e inscrições ilimitados. O Pro adiciona a venda de bilhetes pagos e o Enterprise lugares marcados. Sem taxa de plataforma.',
    'features_title' => 'Software de gestão de eventos: todas as funcionalidades, sem taxas de plataforma',
    'features_description' => 'Todas as funcionalidades em cinco capítulos: bilhetes pelo Stripe ou PayPal sem taxa de plataforma, sincronização de calendário nos dois sentidos, newsletters, importação com IA e análises.',
    'about_description' => 'Quem desenvolve o Event Schedule, sob qual licença é distribuído, onde fica o código-fonte e uma lista do que não faz. Open source, sem taxas de plataforma.',
    'selfhost_title' => 'Calendário de eventos auto-alojado | Todas as funcionalidades grátis no seu servidor',
    'selfhost_description' => 'Auto-aloje o Event Schedule no seu próprio servidor e todas as funcionalidades Pro e Enterprise estão incluídas gratuitamente. Open source, instalação num clique, sem taxas de plataforma, e os seus dados nunca saem da sua infraestrutura.',
    'ticketing_title' => 'Software de venda de bilhetes para eventos, sem taxas de plataforma - Event Schedule',
    'ticketing_description' => 'Venda bilhetes na página do seu evento sem taxas de plataforma. Pagamento com Stripe ou PayPal, reembolsos totais ou parciais e check-in por QR em qualquer telemóvel.',
    'calendar_sync_description' => 'Sincronização bidirecional com Google Calendar, Outlook e qualquer servidor CalDAV, grátis em todos os planos. Participantes adicionam um evento ou subscrevem o feed do seu calendário ao vivo.',
]);
