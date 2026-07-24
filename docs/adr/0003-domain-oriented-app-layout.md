# Domain-oriented application layout

Application code is organised as `App\{Domain}\...` (e.g. `App\Routines\Models`, `App\Routines\Data`, `App\Routines\Http\Controllers`). Shared kernel pieces (base controller, middleware, cross-cutting casts/traits) live under `App\Shared`. Auth and settings remain as their own domains rather than a generic Http dump.
