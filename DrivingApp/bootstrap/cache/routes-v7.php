<?php

app('router')->setCompiledRoutes(
    array (
  'compiled' => 
  array (
    0 => false,
    1 => 
    array (
      '/up' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::LAPF1ru3ImtgPIDY',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'welcome',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/test/course-form' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'test.course-form',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/system-admin/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.login',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.login.submit',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/system-admin' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/system-admin/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/system-admin/schools' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.schools',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.schools.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/system-admin/admins' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.admins',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.admins.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/system-admin/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.users',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/system-admin/logs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.logs',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/system-admin/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.logout',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
    ),
    2 => 
    array (
      0 => '{^(?|/system\\-admin/(?|schools/([^/]++)(?|/toggle\\-status(*:59)|(*:66))|admins/([^/]++)(?|/toggle\\-status(*:107)|(*:115))|users/([^/]++)/([^/]++)(?|/toggle\\-status(*:165)|(*:173))|logs/(?|([^/]++)(?|(*:201)|/resolve(*:217))|cleanup(*:233)))|/([^/]++)(?|(*:255)|/(?|log(?|in(?|(*:278))|out(*:290))|forgot\\-password(?|(*:318))|re(?|se(?|t\\-password(?|/([^/]++)(*:360)|(*:368))|nd\\-verification(*:393))|gister(?|(*:411)))|verify\\-email(?|(*:437))|guest/(?|dashboard(*:464)|courses(*:479)|enroll(?|/([^/]++)(*:505)|ment\\-requests(*:527)))|admin(?|(*:545)|/(?|user\\-management(*:573)|s(?|t(?|ore\\-account(*:601)|udents/([^/]++)(?|(*:627)|/toggle\\-status(*:650)))|chedules(?|(*:671)|/(?|create(*:689)|([^/]++)(?|(*:708))))|e(?|ttings(?|(*:732))|ssions(?|(*:750)|/(?|([^/]++)(?|(*:773))|enrollment/([^/]++)/stats(*:807)))))|instructors(?|(*:833)|/([^/]++)(?|(*:853)|/(?|toggle\\-status(*:879)|availability(*:899))))|re(?|moval\\-requests(?|(*:933)|/([^/]++)/(?|approve(*:961)|reject(*:975)))|ports(?|/(?|students(*:1005)|instructors(*:1025)|logs(*:1038)|export/(?|students(*:1065)|instructors(*:1085)|bookings(*:1102)|payments(*:1119)|courses(*:1135)))|(*:1146)))|courses(?|(*:1167)|/([^/]++)(?|(*:1188)|/(?|packages(?|(*:1212)|/([^/]++)(?|(*:1233)))|modules(?|(*:1254)|/(?|create(*:1273)|([^/]++)(?|(*:1293)|/edit(*:1307)|(*:1316))|reorder(*:1333)|([^/]++)/(?|duplicate(*:1363)|lessons(?|(*:1382)|/(?|create(*:1401)|([^/]++)(?|(*:1421)|/edit(*:1435)|(*:1444))|reorder(*:1461))|(*:1471))))|(*:1483)))))|p(?|rofile(?|(*:1509)|/picture(*:1526))|ayments(?|(*:1546)|/(?|([^/]++)(?|(*:1570))|statistics(*:1590))))|e(?|xports/(?|student(?|s/(?|pdf(*:1634)|excel(*:1648))|/([^/]++)/progress/pdf(*:1680))|enrollments/pdf(*:1705))|nrollments(?|(*:1728)|/(?|bulk\\-(?|approve(*:1757)|reject(*:1772))|([^/]++)/(?|approve(*:1801)|reject(*:1816)|c(?|omplete(*:1836)|ancel(*:1850))|payment\\-status(*:1875)|theoretical\\-passed(*:1903)))))|bookings(?|(*:1927)|/([^/]++)(?|(*:1948)|/status(*:1964)))|theoretical(?|(*:1989)|/(?|passed/list(*:2013)|stats/overview(*:2036)|([^/]++)(*:2053)|mark\\-passed(*:2074)|([^/]++)/revoke(*:2098)))|logout(*:2115)))|instructor(?|(*:2139)|/(?|my\\-schedule(*:2164)|t(?|imeslots/([^/]++)/(?|toggle(*:2204)|request\\-removal(*:2229))|heoretical(?|(*:2252)|/(?|([^/]++)(*:2273)|mark\\-passed(*:2294)|passed/list(*:2314))))|pro(?|file(?|(*:2339)|/picture(*:2356))|gress(?|(*:2374)|/(?|create(*:2393)|([^/]++)(?|(*:2413)|/edit(*:2427)|(*:2436)))|(*:2447)))|bookings/([^/]++)/(?|attendance(*:2489)|feedback(*:2506))|l(?|essons/([^/]++)(?|(*:2538)|/update(*:2554))|ogout(*:2569))|s(?|tudents(?|(*:2593)|/([^/]++)(*:2611))|essions(?|(*:2631)|/(?|create(*:2650)|([^/]++)(?|(*:2670)|/edit(*:2684)|(*:2693))|enrollment/([^/]++)/stats(*:2728))|(*:2738)))|reports(*:2756)|grades(*:2771)|courses/([^/]++)/modules(?|(*:2807)|/([^/]++)(?|(*:2828)|/lessons(?|(*:2848)|/([^/]++)(*:2866))))))|student(?|(*:2890)|/(?|p(?|ro(?|file(?|(*:2919)|/picture(*:2936))|gress(*:2951))|ayments(?|(*:2971)|/([^/]++)(*:2989)))|courses(?|(*:3010)|/([^/]++)(?|(*:3031)|/modules(?|(*:3051)|/([^/]++)(?|(*:3072)|/lessons(?|(*:3092)|/([^/]++)(*:3110))))))|bookings(?|(*:3135)|/([^/]++)/(?|confirm(*:3164)|queue(*:3178)))|schedule(*:3197)|logout(*:3212)))))|/storage/(.*)(*:3238))/?$}sDu',
    ),
    3 => 
    array (
      59 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.schools.toggle-status',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      66 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.schools.delete',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      107 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.admins.toggle-status',
          ),
          1 => 
          array (
            0 => 'admin',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      115 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.admins.delete',
          ),
          1 => 
          array (
            0 => 'admin',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      165 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.users.toggle-status',
          ),
          1 => 
          array (
            0 => 'type',
            1 => 'id',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      173 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.users.delete',
          ),
          1 => 
          array (
            0 => 'type',
            1 => 'id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      201 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.logs.show',
          ),
          1 => 
          array (
            0 => 'log',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      217 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.logs.resolve',
          ),
          1 => 
          array (
            0 => 'log',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      233 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'system-admin.logs.cleanup',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      255 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.login',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      278 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.login.submit',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      290 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.logout',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      318 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.password.request',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.password.email',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      360 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.password.reset',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'token',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      368 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.password.update',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      393 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.verification.resend',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      411 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.registration.form',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.registration.submit',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      437 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.verification.show',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.verification.verify',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      464 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.guest.dashboard',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      479 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.guest.courses',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      505 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.guest.enroll',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      527 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.guest.enrollmentRequests',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      545 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.dashboard',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      573 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.userManagement',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      601 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.storeAccount',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      627 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.students.update',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      650 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.students.toggleStatus',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'id',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      671 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.schedules',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      689 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.schedules.create',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      708 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.schedules.update',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.schedules.delete',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      732 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.settings',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.settings.update',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      750 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.sessions.index',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      773 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.sessions.show',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'sessionCompletion',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.sessions.destroy',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'sessionCompletion',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      807 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.sessions.enrollmentStats',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'enrollment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      833 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.instructors.store',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      853 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.instructors.update',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      879 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.instructors.toggleStatus',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'id',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      899 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.instructors.availability',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'id',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      933 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.removalRequests',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      961 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.removalRequests.approve',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      975 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.removalRequests.reject',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1005 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.reports.students',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1025 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.reports.instructors',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1038 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.reports.logs',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1065 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.reports.export.students',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1085 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.reports.export.instructors',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1102 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.reports.export.bookings',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1119 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.reports.export.payments',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1135 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.reports.export.courses',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1146 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.reports.index',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1167 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.store',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1188 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.update',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.delete',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1212 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.packages.store',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'courseId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1233 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.packages.update',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'courseId',
            2 => 'packageId',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.packages.delete',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'courseId',
            2 => 'packageId',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1254 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.modules.index',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1273 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.modules.create',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1293 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.modules.show',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1307 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.modules.edit',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1316 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.modules.update',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.modules.destroy',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1333 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.modules.reorder',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1363 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.modules.duplicate',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1382 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.modules.lessons.index',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1401 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.modules.lessons.create',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1421 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.modules.lessons.show',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
            3 => 'lesson',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1435 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.modules.lessons.edit',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
            3 => 'lesson',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1444 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.modules.lessons.update',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
            3 => 'lesson',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.modules.lessons.destroy',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
            3 => 'lesson',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1461 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.modules.lessons.reorder',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1471 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.modules.lessons.store',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1483 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.courses.modules.store',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1509 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.profile',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.profile.update',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1526 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.profile.picture',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1546 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.payments.index',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.payments.store',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1570 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.payments.show',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'payment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.payments.update',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'payment',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.payments.destroy',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'payment',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1590 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.payments.statistics',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1634 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.exports.students.pdf',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1648 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.exports.students.excel',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1680 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.exports.student.progress.pdf',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'student',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1705 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.exports.enrollments.pdf',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1728 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.enrollments.index',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1757 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.enrollments.bulkApprove',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1772 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.enrollments.bulkReject',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1801 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.enrollments.approve',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'enrollmentRequest',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1816 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.enrollments.reject',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'enrollmentRequest',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1836 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.enrollments.complete',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'enrollmentRequest',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1850 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.enrollments.cancel',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'enrollmentRequest',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1875 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.enrollments.paymentStatus',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'enrollmentRequest',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1903 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.enrollments.theoreticalPassed',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'enrollmentRequest',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1927 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.bookings.index',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.bookings.store',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1948 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.bookings.show',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'booking',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.bookings.update',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'booking',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.bookings.destroy',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'booking',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1964 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.bookings.updateStatus',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'booking',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1989 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.theoretical.index',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2013 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.theoretical.passed',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2036 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.theoretical.stats',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2053 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.theoretical.show',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'enrollment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2074 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.theoretical.markAsPassed',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2098 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.theoretical.revoke',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'enrollment',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2115 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.admin.logout',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2139 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.dashboard',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2164 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.schedule',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2204 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.timeslots.toggle',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2229 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.timeslots.requestRemoval',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2252 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.theoretical.index',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2273 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.theoretical.show',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'enrollment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2294 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.theoretical.markAsPassed',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2314 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.theoretical.passed',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2339 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.profile',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.profile.update',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2356 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.profile.picture',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2374 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.progress.index',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2393 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.progress.create',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2413 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.progress.show',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'progress',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2427 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.progress.edit',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'progress',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2436 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.progress.update',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'progress',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.progress.destroy',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'progress',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2447 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.progress.store',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2489 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.bookings.attendance',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'booking',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2506 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.bookings.feedback',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'booking',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2538 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.lessons.details',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'booking',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2554 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.lessons.update',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'booking',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2569 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.logout',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2593 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.students.index',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2611 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.students.show',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2631 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.sessions.index',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2650 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.sessions.create',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2670 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.sessions.show',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'sessionCompletion',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2684 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.sessions.edit',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'sessionCompletion',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2693 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.sessions.update',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'sessionCompletion',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.sessions.destroy',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'sessionCompletion',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2728 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.sessions.enrollmentStats',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'enrollment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2738 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.sessions.store',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2756 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.reports',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2771 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.grades',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2807 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.courses.modules.index',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2828 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.courses.modules.show',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2848 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.courses.modules.lessons.index',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2866 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.instructor.courses.modules.lessons.show',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
            3 => 'lesson',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2890 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.dashboard',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2919 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.profile',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.profile.update',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2936 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.profile.picture',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2951 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.progress.index',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2971 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.payments.index',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2989 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.payments.show',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'payment',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3010 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.courses.index',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3031 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.courses.show',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3051 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.courses.modules.index',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3072 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.courses.modules.show',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3092 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.courses.modules.lessons.index',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3110 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.courses.modules.lessons.show',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'course',
            2 => 'module',
            3 => 'lesson',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3135 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.bookings.store',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3164 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.bookings.confirm',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'booking',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3178 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.bookings.removeQueue',
          ),
          1 => 
          array (
            0 => 'school',
            1 => 'booking',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3197 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.schedule',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3212 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'schools.student.logout',
          ),
          1 => 
          array (
            0 => 'school',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3238 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'storage.local',
          ),
          1 => 
          array (
            0 => 'path',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => NULL,
          1 => NULL,
          2 => NULL,
          3 => NULL,
          4 => false,
          5 => false,
          6 => 0,
        ),
      ),
    ),
    4 => NULL,
  ),
  'attributes' => 
  array (
    'generated::LAPF1ru3ImtgPIDY' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'up',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:874:"function () {
                    $exception = null;

                    try {
                        \\Illuminate\\Support\\Facades\\Event::dispatch(new \\Illuminate\\Foundation\\Events\\DiagnosingHealth);
                    } catch (\\Throwable $e) {
                        if (app()->hasDebugModeEnabled()) {
                            throw $e;
                        }

                        report($e);

                        $exception = $e->getMessage();
                    }

                    return response(\\Illuminate\\Support\\Facades\\View::file(\'C:\\\\Users\\\\jcsdi\\\\Documents\\\\Driving School Management System\\\\DrivingApp\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Configuration\'.\'/../resources/health-up.blade.php\', [
                        \'exception\' => $exception,
                    ]), status: $exception ? 500 : 200);
                }";s:5:"scope";s:54:"Illuminate\\Foundation\\Configuration\\ApplicationBuilder";s:4:"this";N;s:4:"self";s:32:"00000000000009370000000000000000";}}',
        'as' => 'generated::LAPF1ru3ImtgPIDY',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'welcome' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '/',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:202:"function () {
    // Eager load schoolSetting to prevent N+1 queries
    $schools = \\App\\Models\\School::with(\'schoolSetting\')->orderBy(\'name\')->get();
    return \\view(\'welcome\', \\compact(\'schools\'));
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"000000000000093d0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'welcome',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'test.course-form' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'test/course-form',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:80:"function() {
        return \\view(\'test-components.course-form-enhanced\');
    }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000009410000000000000000";}}',
        'as' => 'test.course-form',
        'namespace' => NULL,
        'prefix' => '/test',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.login' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'system-admin/login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@showLogin',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@showLogin',
        'as' => 'system-admin.login',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.login.submit' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'system-admin/login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@login',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@login',
        'as' => 'system-admin.login.submit',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'system-admin',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@dashboard',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@dashboard',
        'as' => 'system-admin.dashboard',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'system-admin/dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@dashboard',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@dashboard',
        'as' => 'system-admin.',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.schools' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'system-admin/schools',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@schools',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@schools',
        'as' => 'system-admin.schools',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.schools.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'system-admin/schools',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@storeSchool',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@storeSchool',
        'as' => 'system-admin.schools.store',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.schools.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'system-admin/schools/{school}/toggle-status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@toggleSchoolStatus',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@toggleSchoolStatus',
        'as' => 'system-admin.schools.toggle-status',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.schools.delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'system-admin/schools/{school}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@deleteSchool',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@deleteSchool',
        'as' => 'system-admin.schools.delete',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.admins' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'system-admin/admins',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@admins',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@admins',
        'as' => 'system-admin.admins',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.admins.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'system-admin/admins',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@storeAdmin',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@storeAdmin',
        'as' => 'system-admin.admins.store',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.admins.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'system-admin/admins/{admin}/toggle-status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@toggleAdminStatus',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@toggleAdminStatus',
        'as' => 'system-admin.admins.toggle-status',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.admins.delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'system-admin/admins/{admin}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@deleteAdmin',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@deleteAdmin',
        'as' => 'system-admin.admins.delete',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.users' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'system-admin/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@users',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@users',
        'as' => 'system-admin.users',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.users.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'system-admin/users/{type}/{id}/toggle-status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@toggleUserStatus',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@toggleUserStatus',
        'as' => 'system-admin.users.toggle-status',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.users.delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'system-admin/users/{type}/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@deleteUser',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@deleteUser',
        'as' => 'system-admin.users.delete',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.logs' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'system-admin/logs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@logs',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@logs',
        'as' => 'system-admin.logs',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.logs.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'system-admin/logs/{log}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@showLog',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@showLog',
        'as' => 'system-admin.logs.show',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.logs.resolve' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'system-admin/logs/{log}/resolve',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@resolveLog',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@resolveLog',
        'as' => 'system-admin.logs.resolve',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.logs.cleanup' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'system-admin/logs/cleanup',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@cleanupLogs',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@cleanupLogs',
        'as' => 'system-admin.logs.cleanup',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'system-admin.logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'system-admin/logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'system.admin',
        ),
        'uses' => 'App\\Http\\Controllers\\SystemAdminController@logout',
        'controller' => 'App\\Http\\Controllers\\SystemAdminController@logout',
        'as' => 'system-admin.logout',
        'namespace' => NULL,
        'prefix' => '/system-admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.login' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AuthController@showLogin',
        'controller' => 'App\\Http\\Controllers\\AuthController@showLogin',
        'as' => 'schools.login',
        'namespace' => NULL,
        'prefix' => '/{school:slug}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AuthController@showLogin',
        'controller' => 'App\\Http\\Controllers\\AuthController@showLogin',
        'as' => 'schools.',
        'namespace' => NULL,
        'prefix' => '/{school:slug}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.login.submit' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AuthController@login',
        'controller' => 'App\\Http\\Controllers\\AuthController@login',
        'as' => 'schools.login.submit',
        'namespace' => NULL,
        'prefix' => '/{school:slug}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AuthController@logout',
        'controller' => 'App\\Http\\Controllers\\AuthController@logout',
        'as' => 'schools.logout',
        'namespace' => NULL,
        'prefix' => '/{school:slug}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.password.request' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/forgot-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\PasswordResetController@showForgotForm',
        'controller' => 'App\\Http\\Controllers\\PasswordResetController@showForgotForm',
        'as' => 'schools.password.request',
        'namespace' => NULL,
        'prefix' => '/{school:slug}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.password.email' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/forgot-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\PasswordResetController@sendResetLink',
        'controller' => 'App\\Http\\Controllers\\PasswordResetController@sendResetLink',
        'as' => 'schools.password.email',
        'namespace' => NULL,
        'prefix' => '/{school:slug}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.password.reset' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/reset-password/{token}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\PasswordResetController@showResetForm',
        'controller' => 'App\\Http\\Controllers\\PasswordResetController@showResetForm',
        'as' => 'schools.password.reset',
        'namespace' => NULL,
        'prefix' => '/{school:slug}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.password.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/reset-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\PasswordResetController@reset',
        'controller' => 'App\\Http\\Controllers\\PasswordResetController@reset',
        'as' => 'schools.password.update',
        'namespace' => NULL,
        'prefix' => '/{school:slug}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.registration.form' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/register',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\GuestController@showRegistrationForm',
        'controller' => 'App\\Http\\Controllers\\GuestController@showRegistrationForm',
        'as' => 'schools.registration.form',
        'namespace' => NULL,
        'prefix' => '/{school:slug}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.registration.submit' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/register',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\GuestController@register',
        'controller' => 'App\\Http\\Controllers\\GuestController@register',
        'as' => 'schools.registration.submit',
        'namespace' => NULL,
        'prefix' => '/{school:slug}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.verification.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/verify-email',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\GuestController@showVerificationForm',
        'controller' => 'App\\Http\\Controllers\\GuestController@showVerificationForm',
        'as' => 'schools.verification.show',
        'namespace' => NULL,
        'prefix' => '/{school:slug}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.verification.verify' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/verify-email',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\GuestController@verifyEmail',
        'controller' => 'App\\Http\\Controllers\\GuestController@verifyEmail',
        'as' => 'schools.verification.verify',
        'namespace' => NULL,
        'prefix' => '/{school:slug}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.verification.resend' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/resend-verification',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\GuestController@resendVerificationCode',
        'controller' => 'App\\Http\\Controllers\\GuestController@resendVerificationCode',
        'as' => 'schools.verification.resend',
        'namespace' => NULL,
        'prefix' => '/{school:slug}',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.guest.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/guest/dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'guest.role',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\GuestController@dashboard',
        'controller' => 'App\\Http\\Controllers\\GuestController@dashboard',
        'as' => 'schools.guest.dashboard',
        'namespace' => NULL,
        'prefix' => '{school:slug}/guest',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.guest.courses' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/guest/courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'guest.role',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\GuestController@courses',
        'controller' => 'App\\Http\\Controllers\\GuestController@courses',
        'as' => 'schools.guest.courses',
        'namespace' => NULL,
        'prefix' => '{school:slug}/guest',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.guest.enroll' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/guest/enroll/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'guest.role',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\GuestController@enroll',
        'controller' => 'App\\Http\\Controllers\\GuestController@enroll',
        'as' => 'schools.guest.enroll',
        'namespace' => NULL,
        'prefix' => '{school:slug}/guest',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.guest.enrollmentRequests' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/guest/enrollment-requests',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'guest.role',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\GuestController@enrollmentRequests',
        'controller' => 'App\\Http\\Controllers\\GuestController@enrollmentRequests',
        'as' => 'schools.guest.enrollmentRequests',
        'namespace' => NULL,
        'prefix' => '{school:slug}/guest',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@dashboard',
        'controller' => 'App\\Http\\Controllers\\AdminController@dashboard',
        'as' => 'schools.admin.dashboard',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.userManagement' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/user-management',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@userManagement',
        'controller' => 'App\\Http\\Controllers\\AdminController@userManagement',
        'as' => 'schools.admin.userManagement',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.storeAccount' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/store-account',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@storeAccount',
        'controller' => 'App\\Http\\Controllers\\AdminController@storeAccount',
        'as' => 'schools.admin.storeAccount',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.students.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => '{school}/admin/students/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@updateStudent',
        'controller' => 'App\\Http\\Controllers\\AdminController@updateStudent',
        'as' => 'schools.admin.students.update',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.students.toggleStatus' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => '{school}/admin/students/{id}/toggle-status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@toggleStudentStatus',
        'controller' => 'App\\Http\\Controllers\\AdminController@toggleStudentStatus',
        'as' => 'schools.admin.students.toggleStatus',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.instructors.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/instructors',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@storeAccount',
        'controller' => 'App\\Http\\Controllers\\AdminController@storeAccount',
        'as' => 'schools.admin.instructors.store',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.instructors.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => '{school}/admin/instructors/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@updateInstructor',
        'controller' => 'App\\Http\\Controllers\\AdminController@updateInstructor',
        'as' => 'schools.admin.instructors.update',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.instructors.toggleStatus' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => '{school}/admin/instructors/{id}/toggle-status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@toggleInstructorStatus',
        'controller' => 'App\\Http\\Controllers\\AdminController@toggleInstructorStatus',
        'as' => 'schools.admin.instructors.toggleStatus',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.instructors.availability' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => '{school}/admin/instructors/{id}/availability',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@toggleAvailability',
        'controller' => 'App\\Http\\Controllers\\AdminController@toggleAvailability',
        'as' => 'schools.admin.instructors.availability',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.schedules' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/schedules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@schedules',
        'controller' => 'App\\Http\\Controllers\\AdminController@schedules',
        'as' => 'schools.admin.schedules',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.schedules.create' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/schedules/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@createSchedule',
        'controller' => 'App\\Http\\Controllers\\AdminController@createSchedule',
        'as' => 'schools.admin.schedules.create',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.schedules.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => '{school}/admin/schedules/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@updateSchedule',
        'controller' => 'App\\Http\\Controllers\\AdminController@updateSchedule',
        'as' => 'schools.admin.schedules.update',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.schedules.delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => '{school}/admin/schedules/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@deleteSchedule',
        'controller' => 'App\\Http\\Controllers\\AdminController@deleteSchedule',
        'as' => 'schools.admin.schedules.delete',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.removalRequests' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/removal-requests',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@removalRequests',
        'controller' => 'App\\Http\\Controllers\\AdminController@removalRequests',
        'as' => 'schools.admin.removalRequests',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.removalRequests.approve' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/removal-requests/{id}/approve',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@approveRemovalRequest',
        'controller' => 'App\\Http\\Controllers\\AdminController@approveRemovalRequest',
        'as' => 'schools.admin.removalRequests.approve',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.removalRequests.reject' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/removal-requests/{id}/reject',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@rejectRemovalRequest',
        'controller' => 'App\\Http\\Controllers\\AdminController@rejectRemovalRequest',
        'as' => 'schools.admin.removalRequests.reject',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@courses',
        'controller' => 'App\\Http\\Controllers\\AdminController@courses',
        'as' => 'schools.admin.courses',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@storeCourse',
        'controller' => 'App\\Http\\Controllers\\AdminController@storeCourse',
        'as' => 'schools.admin.courses.store',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => '{school}/admin/courses/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@updateCourse',
        'controller' => 'App\\Http\\Controllers\\AdminController@updateCourse',
        'as' => 'schools.admin.courses.update',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => '{school}/admin/courses/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@deleteCourse',
        'controller' => 'App\\Http\\Controllers\\AdminController@deleteCourse',
        'as' => 'schools.admin.courses.delete',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.packages.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/courses/{courseId}/packages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@storePackage',
        'controller' => 'App\\Http\\Controllers\\AdminController@storePackage',
        'as' => 'schools.admin.courses.packages.store',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.packages.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => '{school}/admin/courses/{courseId}/packages/{packageId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@updatePackage',
        'controller' => 'App\\Http\\Controllers\\AdminController@updatePackage',
        'as' => 'schools.admin.courses.packages.update',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.packages.delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => '{school}/admin/courses/{courseId}/packages/{packageId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@deletePackage',
        'controller' => 'App\\Http\\Controllers\\AdminController@deletePackage',
        'as' => 'schools.admin.courses.packages.delete',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.settings' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@settings',
        'controller' => 'App\\Http\\Controllers\\AdminController@settings',
        'as' => 'schools.admin.settings',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.settings.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@updateSettings',
        'controller' => 'App\\Http\\Controllers\\AdminController@updateSettings',
        'as' => 'schools.admin.settings.update',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.reports.students' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/reports/students',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@studentReports',
        'controller' => 'App\\Http\\Controllers\\AdminController@studentReports',
        'as' => 'schools.admin.reports.students',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.reports.instructors' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/reports/instructors',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@instructorReports',
        'controller' => 'App\\Http\\Controllers\\AdminController@instructorReports',
        'as' => 'schools.admin.reports.instructors',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.reports.logs' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/reports/logs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@logs',
        'controller' => 'App\\Http\\Controllers\\AdminController@logs',
        'as' => 'schools.admin.reports.logs',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.reports.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/reports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ReportController@index',
        'controller' => 'App\\Http\\Controllers\\ReportController@index',
        'as' => 'schools.admin.reports.index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.reports.export.students' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/reports/export/students',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ReportController@exportStudents',
        'controller' => 'App\\Http\\Controllers\\ReportController@exportStudents',
        'as' => 'schools.admin.reports.export.students',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.reports.export.instructors' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/reports/export/instructors',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ReportController@exportInstructors',
        'controller' => 'App\\Http\\Controllers\\ReportController@exportInstructors',
        'as' => 'schools.admin.reports.export.instructors',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.reports.export.bookings' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/reports/export/bookings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ReportController@exportBookings',
        'controller' => 'App\\Http\\Controllers\\ReportController@exportBookings',
        'as' => 'schools.admin.reports.export.bookings',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.reports.export.payments' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/reports/export/payments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ReportController@exportPayments',
        'controller' => 'App\\Http\\Controllers\\ReportController@exportPayments',
        'as' => 'schools.admin.reports.export.payments',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.reports.export.courses' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/reports/export/courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ReportController@exportCourses',
        'controller' => 'App\\Http\\Controllers\\ReportController@exportCourses',
        'as' => 'schools.admin.reports.export.courses',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.profile' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@profile',
        'controller' => 'App\\Http\\Controllers\\AdminController@profile',
        'as' => 'schools.admin.profile',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.profile.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => '{school}/admin/profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@updateProfile',
        'controller' => 'App\\Http\\Controllers\\AdminController@updateProfile',
        'as' => 'schools.admin.profile.update',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.profile.picture' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/profile/picture',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AdminController@updateProfilePicture',
        'controller' => 'App\\Http\\Controllers\\AdminController@updateProfilePicture',
        'as' => 'schools.admin.profile.picture',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.exports.students.pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/exports/students/pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ExportController@studentsPdf',
        'controller' => 'App\\Http\\Controllers\\ExportController@studentsPdf',
        'as' => 'schools.admin.exports.students.pdf',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/exports',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.exports.students.excel' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/exports/students/excel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ExportController@studentsExcel',
        'controller' => 'App\\Http\\Controllers\\ExportController@studentsExcel',
        'as' => 'schools.admin.exports.students.excel',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/exports',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.exports.enrollments.pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/exports/enrollments/pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ExportController@enrollmentsPdf',
        'controller' => 'App\\Http\\Controllers\\ExportController@enrollmentsPdf',
        'as' => 'schools.admin.exports.enrollments.pdf',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/exports',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.exports.student.progress.pdf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/exports/student/{student}/progress/pdf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ExportController@studentProgressPdf',
        'controller' => 'App\\Http\\Controllers\\ExportController@studentProgressPdf',
        'as' => 'schools.admin.exports.student.progress.pdf',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/exports',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.bookings.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/bookings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'as' => 'schools.admin.bookings.index',
        'uses' => 'App\\Http\\Controllers\\BookingController@index',
        'controller' => 'App\\Http\\Controllers\\BookingController@index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.bookings.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/bookings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'as' => 'schools.admin.bookings.store',
        'uses' => 'App\\Http\\Controllers\\BookingController@store',
        'controller' => 'App\\Http\\Controllers\\BookingController@store',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.bookings.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/bookings/{booking}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'as' => 'schools.admin.bookings.show',
        'uses' => 'App\\Http\\Controllers\\BookingController@show',
        'controller' => 'App\\Http\\Controllers\\BookingController@show',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.bookings.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => '{school}/admin/bookings/{booking}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'as' => 'schools.admin.bookings.update',
        'uses' => 'App\\Http\\Controllers\\BookingController@update',
        'controller' => 'App\\Http\\Controllers\\BookingController@update',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.bookings.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => '{school}/admin/bookings/{booking}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'as' => 'schools.admin.bookings.destroy',
        'uses' => 'App\\Http\\Controllers\\BookingController@destroy',
        'controller' => 'App\\Http\\Controllers\\BookingController@destroy',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.bookings.updateStatus' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => '{school}/admin/bookings/{booking}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\BookingController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\BookingController@updateStatus',
        'as' => 'schools.admin.bookings.updateStatus',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.payments.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/payments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'as' => 'schools.admin.payments.index',
        'uses' => 'App\\Http\\Controllers\\PaymentController@index',
        'controller' => 'App\\Http\\Controllers\\PaymentController@index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.payments.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/payments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'as' => 'schools.admin.payments.store',
        'uses' => 'App\\Http\\Controllers\\PaymentController@store',
        'controller' => 'App\\Http\\Controllers\\PaymentController@store',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.payments.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/payments/{payment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'as' => 'schools.admin.payments.show',
        'uses' => 'App\\Http\\Controllers\\PaymentController@show',
        'controller' => 'App\\Http\\Controllers\\PaymentController@show',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.payments.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => '{school}/admin/payments/{payment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'as' => 'schools.admin.payments.update',
        'uses' => 'App\\Http\\Controllers\\PaymentController@update',
        'controller' => 'App\\Http\\Controllers\\PaymentController@update',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.payments.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => '{school}/admin/payments/{payment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'as' => 'schools.admin.payments.destroy',
        'uses' => 'App\\Http\\Controllers\\PaymentController@destroy',
        'controller' => 'App\\Http\\Controllers\\PaymentController@destroy',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.payments.statistics' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/payments/statistics',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\PaymentController@statistics',
        'controller' => 'App\\Http\\Controllers\\PaymentController@statistics',
        'as' => 'schools.admin.payments.statistics',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.enrollments.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/enrollments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\EnrollmentRequestController@index',
        'controller' => 'App\\Http\\Controllers\\EnrollmentRequestController@index',
        'as' => 'schools.admin.enrollments.index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/enrollments',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.enrollments.bulkApprove' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/enrollments/bulk-approve',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\EnrollmentRequestController@bulkApprove',
        'controller' => 'App\\Http\\Controllers\\EnrollmentRequestController@bulkApprove',
        'as' => 'schools.admin.enrollments.bulkApprove',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/enrollments',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.enrollments.bulkReject' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/enrollments/bulk-reject',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\EnrollmentRequestController@bulkReject',
        'controller' => 'App\\Http\\Controllers\\EnrollmentRequestController@bulkReject',
        'as' => 'schools.admin.enrollments.bulkReject',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/enrollments',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.enrollments.approve' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/enrollments/{enrollmentRequest}/approve',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\EnrollmentRequestController@approve',
        'controller' => 'App\\Http\\Controllers\\EnrollmentRequestController@approve',
        'as' => 'schools.admin.enrollments.approve',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/enrollments',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.enrollments.reject' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/enrollments/{enrollmentRequest}/reject',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\EnrollmentRequestController@reject',
        'controller' => 'App\\Http\\Controllers\\EnrollmentRequestController@reject',
        'as' => 'schools.admin.enrollments.reject',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/enrollments',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.enrollments.complete' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/enrollments/{enrollmentRequest}/complete',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\EnrollmentRequestController@complete',
        'controller' => 'App\\Http\\Controllers\\EnrollmentRequestController@complete',
        'as' => 'schools.admin.enrollments.complete',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/enrollments',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.enrollments.cancel' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/enrollments/{enrollmentRequest}/cancel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\EnrollmentRequestController@cancel',
        'controller' => 'App\\Http\\Controllers\\EnrollmentRequestController@cancel',
        'as' => 'schools.admin.enrollments.cancel',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/enrollments',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.enrollments.paymentStatus' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/enrollments/{enrollmentRequest}/payment-status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\EnrollmentRequestController@updatePaymentStatus',
        'controller' => 'App\\Http\\Controllers\\EnrollmentRequestController@updatePaymentStatus',
        'as' => 'schools.admin.enrollments.paymentStatus',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/enrollments',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.enrollments.theoreticalPassed' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/enrollments/{enrollmentRequest}/theoretical-passed',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\EnrollmentRequestController@markTheoreticalPassed',
        'controller' => 'App\\Http\\Controllers\\EnrollmentRequestController@markTheoreticalPassed',
        'as' => 'schools.admin.enrollments.theoreticalPassed',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/enrollments',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.theoretical.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/theoretical',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\TheoreticalCompletionController@index',
        'controller' => 'App\\Http\\Controllers\\TheoreticalCompletionController@index',
        'as' => 'schools.admin.theoretical.index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/theoretical',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.theoretical.passed' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/theoretical/passed/list',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\TheoreticalCompletionController@passed',
        'controller' => 'App\\Http\\Controllers\\TheoreticalCompletionController@passed',
        'as' => 'schools.admin.theoretical.passed',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/theoretical',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.theoretical.stats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/theoretical/stats/overview',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\TheoreticalCompletionController@stats',
        'controller' => 'App\\Http\\Controllers\\TheoreticalCompletionController@stats',
        'as' => 'schools.admin.theoretical.stats',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/theoretical',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.theoretical.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/theoretical/{enrollment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\TheoreticalCompletionController@show',
        'controller' => 'App\\Http\\Controllers\\TheoreticalCompletionController@show',
        'as' => 'schools.admin.theoretical.show',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/theoretical',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.theoretical.markAsPassed' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/theoretical/mark-passed',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\TheoreticalCompletionController@markAsPassed',
        'controller' => 'App\\Http\\Controllers\\TheoreticalCompletionController@markAsPassed',
        'as' => 'schools.admin.theoretical.markAsPassed',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/theoretical',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.theoretical.revoke' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/theoretical/{enrollment}/revoke',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\TheoreticalCompletionController@revoke',
        'controller' => 'App\\Http\\Controllers\\TheoreticalCompletionController@revoke',
        'as' => 'schools.admin.theoretical.revoke',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/theoretical',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.sessions.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/sessions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\SessionCompletionController@index',
        'controller' => 'App\\Http\\Controllers\\SessionCompletionController@index',
        'as' => 'schools.admin.sessions.index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/sessions',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.sessions.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/sessions/{sessionCompletion}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\SessionCompletionController@show',
        'controller' => 'App\\Http\\Controllers\\SessionCompletionController@show',
        'as' => 'schools.admin.sessions.show',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/sessions',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.sessions.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => '{school}/admin/sessions/{sessionCompletion}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\SessionCompletionController@destroy',
        'controller' => 'App\\Http\\Controllers\\SessionCompletionController@destroy',
        'as' => 'schools.admin.sessions.destroy',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/sessions',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.sessions.enrollmentStats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/sessions/enrollment/{enrollment}/stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\SessionCompletionController@enrollmentStats',
        'controller' => 'App\\Http\\Controllers\\SessionCompletionController@enrollmentStats',
        'as' => 'schools.admin.sessions.enrollmentStats',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/sessions',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.modules.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/courses/{course}/modules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\CourseModuleController@index',
        'controller' => 'App\\Http\\Controllers\\CourseModuleController@index',
        'as' => 'schools.admin.courses.modules.index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/courses/{course}/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.modules.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/courses/{course}/modules/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\CourseModuleController@create',
        'controller' => 'App\\Http\\Controllers\\CourseModuleController@create',
        'as' => 'schools.admin.courses.modules.create',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/courses/{course}/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.modules.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/courses/{course}/modules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\CourseModuleController@store',
        'controller' => 'App\\Http\\Controllers\\CourseModuleController@store',
        'as' => 'schools.admin.courses.modules.store',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/courses/{course}/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.modules.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/courses/{course}/modules/{module}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\CourseModuleController@show',
        'controller' => 'App\\Http\\Controllers\\CourseModuleController@show',
        'as' => 'schools.admin.courses.modules.show',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/courses/{course}/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.modules.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/courses/{course}/modules/{module}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\CourseModuleController@edit',
        'controller' => 'App\\Http\\Controllers\\CourseModuleController@edit',
        'as' => 'schools.admin.courses.modules.edit',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/courses/{course}/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.modules.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => '{school}/admin/courses/{course}/modules/{module}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\CourseModuleController@update',
        'controller' => 'App\\Http\\Controllers\\CourseModuleController@update',
        'as' => 'schools.admin.courses.modules.update',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/courses/{course}/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.modules.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => '{school}/admin/courses/{course}/modules/{module}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\CourseModuleController@destroy',
        'controller' => 'App\\Http\\Controllers\\CourseModuleController@destroy',
        'as' => 'schools.admin.courses.modules.destroy',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/courses/{course}/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.modules.reorder' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/courses/{course}/modules/reorder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\CourseModuleController@reorder',
        'controller' => 'App\\Http\\Controllers\\CourseModuleController@reorder',
        'as' => 'schools.admin.courses.modules.reorder',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/courses/{course}/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.modules.duplicate' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/courses/{course}/modules/{module}/duplicate',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\CourseModuleController@duplicate',
        'controller' => 'App\\Http\\Controllers\\CourseModuleController@duplicate',
        'as' => 'schools.admin.courses.modules.duplicate',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/courses/{course}/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.modules.lessons.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/courses/{course}/modules/{module}/lessons',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ModuleLessonController@index',
        'controller' => 'App\\Http\\Controllers\\ModuleLessonController@index',
        'as' => 'schools.admin.courses.modules.lessons.index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/courses/{course}/modules/{module}/lessons',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.modules.lessons.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/courses/{course}/modules/{module}/lessons/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ModuleLessonController@create',
        'controller' => 'App\\Http\\Controllers\\ModuleLessonController@create',
        'as' => 'schools.admin.courses.modules.lessons.create',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/courses/{course}/modules/{module}/lessons',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.modules.lessons.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/courses/{course}/modules/{module}/lessons',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ModuleLessonController@store',
        'controller' => 'App\\Http\\Controllers\\ModuleLessonController@store',
        'as' => 'schools.admin.courses.modules.lessons.store',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/courses/{course}/modules/{module}/lessons',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.modules.lessons.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/courses/{course}/modules/{module}/lessons/{lesson}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ModuleLessonController@show',
        'controller' => 'App\\Http\\Controllers\\ModuleLessonController@show',
        'as' => 'schools.admin.courses.modules.lessons.show',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/courses/{course}/modules/{module}/lessons',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.modules.lessons.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/admin/courses/{course}/modules/{module}/lessons/{lesson}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ModuleLessonController@edit',
        'controller' => 'App\\Http\\Controllers\\ModuleLessonController@edit',
        'as' => 'schools.admin.courses.modules.lessons.edit',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/courses/{course}/modules/{module}/lessons',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.modules.lessons.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => '{school}/admin/courses/{course}/modules/{module}/lessons/{lesson}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ModuleLessonController@update',
        'controller' => 'App\\Http\\Controllers\\ModuleLessonController@update',
        'as' => 'schools.admin.courses.modules.lessons.update',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/courses/{course}/modules/{module}/lessons',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.modules.lessons.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => '{school}/admin/courses/{course}/modules/{module}/lessons/{lesson}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ModuleLessonController@destroy',
        'controller' => 'App\\Http\\Controllers\\ModuleLessonController@destroy',
        'as' => 'schools.admin.courses.modules.lessons.destroy',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/courses/{course}/modules/{module}/lessons',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.courses.modules.lessons.reorder' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/courses/{course}/modules/{module}/lessons/reorder',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ModuleLessonController@reorder',
        'controller' => 'App\\Http\\Controllers\\ModuleLessonController@reorder',
        'as' => 'schools.admin.courses.modules.lessons.reorder',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin/courses/{course}/modules/{module}/lessons',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.admin.logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/admin/logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:admin',
          3 => 'redirect.system.admin',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AuthController@logout',
        'controller' => 'App\\Http\\Controllers\\AuthController@logout',
        'as' => 'schools.admin.logout',
        'namespace' => NULL,
        'prefix' => '{school:slug}/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\InstructorController@dashboard',
        'controller' => 'App\\Http\\Controllers\\InstructorController@dashboard',
        'as' => 'schools.instructor.dashboard',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.schedule' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/my-schedule',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\InstructorTimeSlotController@mySchedule',
        'controller' => 'App\\Http\\Controllers\\InstructorTimeSlotController@mySchedule',
        'as' => 'schools.instructor.schedule',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.timeslots.toggle' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/instructor/timeslots/{id}/toggle',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\InstructorTimeSlotController@toggle',
        'controller' => 'App\\Http\\Controllers\\InstructorTimeSlotController@toggle',
        'as' => 'schools.instructor.timeslots.toggle',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.timeslots.requestRemoval' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/instructor/timeslots/{id}/request-removal',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\InstructorTimeSlotController@requestRemoval',
        'controller' => 'App\\Http\\Controllers\\InstructorTimeSlotController@requestRemoval',
        'as' => 'schools.instructor.timeslots.requestRemoval',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.profile' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\InstructorTimeSlotController@profile',
        'controller' => 'App\\Http\\Controllers\\InstructorTimeSlotController@profile',
        'as' => 'schools.instructor.profile',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.profile.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => '{school}/instructor/profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\InstructorTimeSlotController@updateProfile',
        'controller' => 'App\\Http\\Controllers\\InstructorTimeSlotController@updateProfile',
        'as' => 'schools.instructor.profile.update',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.profile.picture' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/instructor/profile/picture',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\InstructorTimeSlotController@updateProfilePicture',
        'controller' => 'App\\Http\\Controllers\\InstructorTimeSlotController@updateProfilePicture',
        'as' => 'schools.instructor.profile.picture',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.bookings.attendance' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/instructor/bookings/{booking}/attendance',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\InstructorTimeSlotController@updateAttendance',
        'controller' => 'App\\Http\\Controllers\\InstructorTimeSlotController@updateAttendance',
        'as' => 'schools.instructor.bookings.attendance',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.bookings.feedback' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/instructor/bookings/{booking}/feedback',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\InstructorTimeSlotController@updateFeedback',
        'controller' => 'App\\Http\\Controllers\\InstructorTimeSlotController@updateFeedback',
        'as' => 'schools.instructor.bookings.feedback',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.lessons.details' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/lessons/{booking}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\InstructorTimeSlotController@getLessonDetails',
        'controller' => 'App\\Http\\Controllers\\InstructorTimeSlotController@getLessonDetails',
        'as' => 'schools.instructor.lessons.details',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.lessons.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/instructor/lessons/{booking}/update',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\InstructorTimeSlotController@updateLessonDetails',
        'controller' => 'App\\Http\\Controllers\\InstructorTimeSlotController@updateLessonDetails',
        'as' => 'schools.instructor.lessons.update',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.students.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/students',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\InstructorController@myStudents',
        'controller' => 'App\\Http\\Controllers\\InstructorController@myStudents',
        'as' => 'schools.instructor.students.index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.students.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/students/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\InstructorController@showStudent',
        'controller' => 'App\\Http\\Controllers\\InstructorController@showStudent',
        'as' => 'schools.instructor.students.show',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.progress.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/progress',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ProgressController@index',
        'controller' => 'App\\Http\\Controllers\\ProgressController@index',
        'as' => 'schools.instructor.progress.index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.progress.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/progress/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ProgressController@create',
        'controller' => 'App\\Http\\Controllers\\ProgressController@create',
        'as' => 'schools.instructor.progress.create',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.progress.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/instructor/progress',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ProgressController@store',
        'controller' => 'App\\Http\\Controllers\\ProgressController@store',
        'as' => 'schools.instructor.progress.store',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.progress.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/progress/{progress}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ProgressController@show',
        'controller' => 'App\\Http\\Controllers\\ProgressController@show',
        'as' => 'schools.instructor.progress.show',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.progress.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/progress/{progress}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ProgressController@edit',
        'controller' => 'App\\Http\\Controllers\\ProgressController@edit',
        'as' => 'schools.instructor.progress.edit',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.progress.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => '{school}/instructor/progress/{progress}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ProgressController@update',
        'controller' => 'App\\Http\\Controllers\\ProgressController@update',
        'as' => 'schools.instructor.progress.update',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.progress.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => '{school}/instructor/progress/{progress}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ProgressController@destroy',
        'controller' => 'App\\Http\\Controllers\\ProgressController@destroy',
        'as' => 'schools.instructor.progress.destroy',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.reports' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/reports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\InstructorController@reports',
        'controller' => 'App\\Http\\Controllers\\InstructorController@reports',
        'as' => 'schools.instructor.reports',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.grades' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/grades',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
          3 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\InstructorController@grades',
        'controller' => 'App\\Http\\Controllers\\InstructorController@grades',
        'as' => 'schools.instructor.grades',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.sessions.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/sessions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\SessionCompletionController@index',
        'controller' => 'App\\Http\\Controllers\\SessionCompletionController@index',
        'as' => 'schools.instructor.sessions.index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor/sessions',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.sessions.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/sessions/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\SessionCompletionController@create',
        'controller' => 'App\\Http\\Controllers\\SessionCompletionController@create',
        'as' => 'schools.instructor.sessions.create',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor/sessions',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.sessions.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/instructor/sessions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\SessionCompletionController@store',
        'controller' => 'App\\Http\\Controllers\\SessionCompletionController@store',
        'as' => 'schools.instructor.sessions.store',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor/sessions',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.sessions.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/sessions/{sessionCompletion}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\SessionCompletionController@show',
        'controller' => 'App\\Http\\Controllers\\SessionCompletionController@show',
        'as' => 'schools.instructor.sessions.show',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor/sessions',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.sessions.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/sessions/{sessionCompletion}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\SessionCompletionController@edit',
        'controller' => 'App\\Http\\Controllers\\SessionCompletionController@edit',
        'as' => 'schools.instructor.sessions.edit',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor/sessions',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.sessions.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => '{school}/instructor/sessions/{sessionCompletion}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\SessionCompletionController@update',
        'controller' => 'App\\Http\\Controllers\\SessionCompletionController@update',
        'as' => 'schools.instructor.sessions.update',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor/sessions',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.sessions.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => '{school}/instructor/sessions/{sessionCompletion}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\SessionCompletionController@destroy',
        'controller' => 'App\\Http\\Controllers\\SessionCompletionController@destroy',
        'as' => 'schools.instructor.sessions.destroy',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor/sessions',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.sessions.enrollmentStats' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/sessions/enrollment/{enrollment}/stats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\SessionCompletionController@enrollmentStats',
        'controller' => 'App\\Http\\Controllers\\SessionCompletionController@enrollmentStats',
        'as' => 'schools.instructor.sessions.enrollmentStats',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor/sessions',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.theoretical.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/theoretical',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\TheoreticalCompletionController@index',
        'controller' => 'App\\Http\\Controllers\\TheoreticalCompletionController@index',
        'as' => 'schools.instructor.theoretical.index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor/theoretical',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.theoretical.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/theoretical/{enrollment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\TheoreticalCompletionController@show',
        'controller' => 'App\\Http\\Controllers\\TheoreticalCompletionController@show',
        'as' => 'schools.instructor.theoretical.show',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor/theoretical',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.theoretical.markAsPassed' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/instructor/theoretical/mark-passed',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\TheoreticalCompletionController@markAsPassed',
        'controller' => 'App\\Http\\Controllers\\TheoreticalCompletionController@markAsPassed',
        'as' => 'schools.instructor.theoretical.markAsPassed',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor/theoretical',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.theoretical.passed' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/theoretical/passed/list',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\TheoreticalCompletionController@passed',
        'controller' => 'App\\Http\\Controllers\\TheoreticalCompletionController@passed',
        'as' => 'schools.instructor.theoretical.passed',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor/theoretical',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.courses.modules.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/courses/{course}/modules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\CourseModuleController@index',
        'controller' => 'App\\Http\\Controllers\\CourseModuleController@index',
        'as' => 'schools.instructor.courses.modules.index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor/courses/{course}/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.courses.modules.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/courses/{course}/modules/{module}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\CourseModuleController@show',
        'controller' => 'App\\Http\\Controllers\\CourseModuleController@show',
        'as' => 'schools.instructor.courses.modules.show',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor/courses/{course}/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.courses.modules.lessons.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/courses/{course}/modules/{module}/lessons',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ModuleLessonController@index',
        'controller' => 'App\\Http\\Controllers\\ModuleLessonController@index',
        'as' => 'schools.instructor.courses.modules.lessons.index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor/courses/{course}/modules/{module}/lessons',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.courses.modules.lessons.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/instructor/courses/{course}/modules/{module}/lessons/{lesson}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ModuleLessonController@show',
        'controller' => 'App\\Http\\Controllers\\ModuleLessonController@show',
        'as' => 'schools.instructor.courses.modules.lessons.show',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor/courses/{course}/modules/{module}/lessons',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.instructor.logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/instructor/logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:instructor',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AuthController@logout',
        'controller' => 'App\\Http\\Controllers\\AuthController@logout',
        'as' => 'schools.instructor.logout',
        'namespace' => NULL,
        'prefix' => '{school:slug}/instructor',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/student',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\StudentController@dashboard',
        'controller' => 'App\\Http\\Controllers\\StudentController@dashboard',
        'as' => 'schools.student.dashboard',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.profile' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/student/profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\StudentController@profile',
        'controller' => 'App\\Http\\Controllers\\StudentController@profile',
        'as' => 'schools.student.profile',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.profile.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => '{school}/student/profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\StudentController@updateProfile',
        'controller' => 'App\\Http\\Controllers\\StudentController@updateProfile',
        'as' => 'schools.student.profile.update',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.profile.picture' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/student/profile/picture',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\StudentController@updateProfilePicture',
        'controller' => 'App\\Http\\Controllers\\StudentController@updateProfilePicture',
        'as' => 'schools.student.profile.picture',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.courses.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/student/courses',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\CourseController@index',
        'controller' => 'App\\Http\\Controllers\\CourseController@index',
        'as' => 'schools.student.courses.index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.courses.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/student/courses/{course}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\CourseController@show',
        'controller' => 'App\\Http\\Controllers\\CourseController@show',
        'as' => 'schools.student.courses.show',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.bookings.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/student/bookings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\BookingController@store',
        'controller' => 'App\\Http\\Controllers\\BookingController@store',
        'as' => 'schools.student.bookings.store',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.bookings.confirm' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/student/bookings/{booking}/confirm',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\BookingController@confirmBooking',
        'controller' => 'App\\Http\\Controllers\\BookingController@confirmBooking',
        'as' => 'schools.student.bookings.confirm',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.bookings.removeQueue' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => '{school}/student/bookings/{booking}/queue',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\BookingController@removeFromQueue',
        'controller' => 'App\\Http\\Controllers\\BookingController@removeFromQueue',
        'as' => 'schools.student.bookings.removeQueue',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.progress.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/student/progress',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ProgressController@index',
        'controller' => 'App\\Http\\Controllers\\ProgressController@index',
        'as' => 'schools.student.progress.index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.payments.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/student/payments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\PaymentController@index',
        'controller' => 'App\\Http\\Controllers\\PaymentController@index',
        'as' => 'schools.student.payments.index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.payments.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/student/payments/{payment}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\PaymentController@show',
        'controller' => 'App\\Http\\Controllers\\PaymentController@show',
        'as' => 'schools.student.payments.show',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.schedule' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/student/schedule',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
          4 => 'ajax',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\StudentController@schedule',
        'controller' => 'App\\Http\\Controllers\\StudentController@schedule',
        'as' => 'schools.student.schedule',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.courses.modules.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/student/courses/{course}/modules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\CourseModuleController@index',
        'controller' => 'App\\Http\\Controllers\\CourseModuleController@index',
        'as' => 'schools.student.courses.modules.index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student/courses/{course}/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.courses.modules.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/student/courses/{course}/modules/{module}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\CourseModuleController@show',
        'controller' => 'App\\Http\\Controllers\\CourseModuleController@show',
        'as' => 'schools.student.courses.modules.show',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student/courses/{course}/modules',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.courses.modules.lessons.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/student/courses/{course}/modules/{module}/lessons',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ModuleLessonController@index',
        'controller' => 'App\\Http\\Controllers\\ModuleLessonController@index',
        'as' => 'schools.student.courses.modules.lessons.index',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student/courses/{course}/modules/{module}/lessons',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.courses.modules.lessons.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{school}/student/courses/{course}/modules/{module}/lessons/{lesson}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\ModuleLessonController@show',
        'controller' => 'App\\Http\\Controllers\\ModuleLessonController@show',
        'as' => 'schools.student.courses.modules.lessons.show',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student/courses/{course}/modules/{module}/lessons',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'schools.student.logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '{school}/student/logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'school.context',
          2 => 'auth:student',
          3 => 'student.role',
        ),
        'scope_bindings' => true,
        'uses' => 'App\\Http\\Controllers\\AuthController@logout',
        'controller' => 'App\\Http\\Controllers\\AuthController@logout',
        'as' => 'schools.student.logout',
        'namespace' => NULL,
        'prefix' => '{school:slug}/student',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
        'school' => 'slug',
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'storage.local' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'storage/{path}',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:3:{s:4:"disk";s:5:"local";s:6:"config";a:5:{s:6:"driver";s:5:"local";s:4:"root";s:88:"C:\\Users\\jcsdi\\Documents\\Driving School Management System\\DrivingApp\\storage\\app/private";s:5:"serve";b:1;s:5:"throw";b:0;s:6:"report";b:0;}s:12:"isProduction";b:0;}s:8:"function";s:323:"function (\\Illuminate\\Http\\Request $request, string $path) use ($disk, $config, $isProduction) {
                    return (new \\Illuminate\\Filesystem\\ServeFile(
                        $disk,
                        $config,
                        $isProduction
                    ))($request, $path);
                }";s:5:"scope";s:47:"Illuminate\\Filesystem\\FilesystemServiceProvider";s:4:"this";N;s:4:"self";s:32:"000000000000093f0000000000000000";}}',
        'as' => 'storage.local',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'path' => '.*',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
  ),
)
);
