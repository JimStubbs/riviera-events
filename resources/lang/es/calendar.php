<?php

return [

    // Layout / Header
    'site_title'    => 'Calendario de Eventos Riviera Maya',
    'add_event'     => 'Agregar Tu Evento',
    'view_events'   => '← Ver Eventos',
    'login'         => 'Iniciar sesión',
    'my_events'     => 'Mis Eventos',
    'all_rights'    => 'Todos los derechos reservados.',

    // Filter bar
    'search'             => 'Buscar',
    'search_placeholder' => 'Eventos, organizadores...',
    'location_label'     => 'Ubicación',
    'all_locations'      => 'Todas las Ubicaciones',
    'category_label'     => 'Categoría',
    'all_categories'     => 'Todas las Categorías',
    'from_label'         => 'Desde',
    'to_label'           => 'Hasta',
    'featured_only'      => '★ Solo destacados',
    'view_list'          => 'Vista de lista',
    'view_month'         => 'Vista mensual',
    'view_week'          => 'Vista semanal',
    'view_day'           => 'Vista diaria',

    // Cards / badges
    'featured_badge' => '★ Destacado',
    'premium_badge'  => '★ Premium',
    'view_event'     => 'Ver Evento →',

    // JS bridge — navigation
    'prev' => '← Ant.',
    'next' => 'Sig. →',

    // JS bridge — calendar UI strings
    'all_day'       => 'Todo el día',
    'no_events_day' => 'No hay eventos este día',

    // JS bridge — locale string passed to toLocaleDateString / toLocaleTimeString
    'js_locale' => 'es-MX',

    // JS bridge — month names (index 0 = Enero)
    'months' => [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
    ],

    // JS bridge — abbreviated month names (index 0 = Ene)
    'months_short' => [
        'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
        'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic',
    ],

    // JS bridge — short day names Lun→Dom (week view order)
    'days_short' => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],

    // Index page
    'no_events_found' => 'No se encontraron eventos',
    'adjust_filters'  => 'Intenta ajustar tus filtros.',
    'page_title'      => 'Calendario de Eventos Riviera Maya — Próximos Eventos',
    'og_description'  => 'Descubre los próximos eventos en la Riviera Maya — Puerto Aventuras, Playa del Carmen, Tulum y más.',

    // Event detail (show) page
    'add_google_cal'    => '+ Agregar a Google Calendar',
    'download_ics'      => '↓ Descargar .ics',
    'event_website'     => '🔗 Sitio web del evento',
    'share'             => 'Compartir con amigos',
    'share_modal_title' => 'Compartir con amigos',
    'copy'              => 'Copiar',
    'copied'            => '¡Copiado!',
    'link_copied'       => '¡Enlace copiado!',
    'email_check_out'   => 'Mira este evento: ',
    'sponsored'         => 'Patrocinado',
    'close'             => 'Cerrar',

    // Submit form
    'submit_title_page'     => 'Enviar un Evento',
    'submit_heading'        => 'Envía un Evento Gratis',
    'submit_subheading'     => 'Al enviar, recibirás un correo de verificación. Tu evento se publica una vez que nuestro equipo lo apruebe.',
    'field_title'           => 'Título del Evento *',
    'field_organizer'       => 'Organizador / Anfitrión *',
    'field_location'        => 'Ubicación *',
    'field_category'        => 'Categoría *',
    'field_description'     => 'Descripción *',
    'field_website'         => 'Sitio web del evento (opcional)',
    'field_image'           => 'Imagen del evento (opcional, máx. 4MB)',
    'field_email'           => 'Tu correo electrónico *',
    'email_note'            => 'Te enviaremos un enlace de verificación a esta dirección.',
    'select_location'       => 'Selecciona ubicación',
    'select_category'       => 'Selecciona categoría',
    'select_pattern'        => 'Selecciona patrón',
    'all_day_event'         => 'Evento de todo el día',
    'field_start_date'      => 'Fecha de inicio *',
    'field_start_time'      => 'Hora de inicio',
    'field_end_date'        => 'Fecha de fin',
    'field_end_time'        => 'Hora de fin',
    'is_recurring'          => 'Este es un evento recurrente',
    'repeats_label'         => 'Se repite *',
    'weekly_same_day'       => 'Semanalmente (mismo día de la semana)',
    'monthly_date'          => 'Mensualmente (misma fecha) — los meses sin esta fecha se omiten',
    'monthly_weekday'       => 'Mensualmente (misma posición del día, p.ej. 3er lunes)',
    'day_of_week'           => 'Día de la semana *',
    'week_of_month'         => 'Semana del mes *',
    'weekday_label'         => 'Día de la semana *',
    'repeat_until'          => 'Repetir hasta (inclusive) *',
    'max_occurrences'       => 'Se generarán máximo 52 ocurrencias.',
    'day_sunday'            => 'Domingo',
    'day_monday'            => 'Lunes',
    'day_tuesday'           => 'Martes',
    'day_wednesday'         => 'Miércoles',
    'day_thursday'          => 'Jueves',
    'day_friday'            => 'Viernes',
    'day_saturday'          => 'Sábado',
    'featured_addon_label'  => '★ Destacar Este Evento — 200 MXN (~$10 USD)',
    'featured_addon_desc'   => 'Tu evento aparecerá en el carrusel de Destacados en la parte superior del calendario y se resaltará con una estrella en todas las vistas. El destacado dura 30 días desde el pago. Al enviar, serás redirigido a Stripe para completar el pago.',
    'featured_addon_note'   => 'Tu enlace de verificación se enviará de inmediato, sin necesidad de esperar el pago primero.',
    'submit_button'         => 'Enviar Evento',
    'cancel'                => 'Cancelar',

    // Category translations (keyed by English DB name)
    'categories' => [
        'Music'                  => 'Música',
        'Food & Drink'           => 'Comida y Bebida',
        'Arts & Culture'         => 'Arte y Cultura',
        'Sports & Fitness'       => 'Deportes y Fitness',
        'Family & Kids'          => 'Familia y Niños',
        'Nightlife'              => 'Vida Nocturna',
        'Business & Networking'  => 'Negocios y Networking',
        'Health & Wellness'      => 'Salud y Bienestar',
        'Charity & Causes'       => 'Caridad y Causas',
        'Outdoors & Adventure'   => 'Aire Libre y Aventura',
        'Film & Media'           => 'Cine y Medios',
        'Holiday & Seasonal'     => 'Fiestas y Temporadas',
    ],
];
