<x-admin.layouts.app :pageTitle="'Usuario: ' . ($user['email'] ?? '')">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $user['email'] }}</h1>
                <div class="flex items-center gap-3 mt-1">
                    <span class="badge {{ ($user['role'] ?? '') === 'admin' ? 'badge-purple' : 'badge-blue' }}">{{ ucfirst($user['role'] ?? '') }}</span>
                    <span class="badge {{ ($user['status'] ?? '') === 'active' ? 'badge-green' : (($user['status'] ?? '') === 'suspended' ? 'badge-amber' : 'badge-red') }}">{{ ucfirst($user['status'] ?? '') }}</span>
                    <span class="badge {{ 
                        ($user['verification_status'] ?? '') === 'verified' ? 'badge-green' : 
                        (($user['verification_status'] ?? '') === 'pending' ? 'badge-amber' : 'badge-gray') }}">
                        {{ ucfirst($user['verification_status'] ?? '') }}
                    </span>
                    @if (($user['is_online'] ?? false))
                        <span class="badge badge-green"><i class="fas fa-circle text-xs mr-1"></i>Online</span>
                    @endif
                </div>
            </div>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('admin.users.edit', $user['id']) }}" class="bg-blue-100 text-blue-700 py-2 px-4 rounded-lg font-medium hover:bg-blue-200">
                    <i class="fas fa-edit"></i> Editar
                </a>
                @if (auth()->id() !== ($user['id'] ?? ''))
                    @if (($user['status'] ?? '') === 'active')
                        <form action="{{ route('admin.users.suspend', $user['id']) }}" method="POST" class="inline" onsubmit="return confirm('¿Suspender a este usuario?')">
                            @csrf
                            <button type="submit" class="bg-amber-100 text-amber-700 py-2 px-4 rounded-lg font-medium hover:bg-amber-200">Suspender</button>
                        </form>
                    @else
                        <form action="{{ route('admin.users.activate', $user['id']) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-100 text-green-700 py-2 px-4 rounded-lg font-medium hover:bg-green-200">Activar</button>
                        </form>
                    @endif
                    
                    @if (($user['status'] ?? '') !== 'banned')
                        <form action="{{ route('admin.users.ban', $user['id']) }}" method="POST" class="inline" onsubmit="return confirm('¿Banear a este usuario? Perderá acceso a su cuenta.')">
                            @csrf
                            <button type="submit" class="bg-red-100 text-red-700 py-2 px-4 rounded-lg font-medium hover:bg-red-200">Banear</button>
                        </form>
                    @endif
                    
                    <form action="{{ route('admin.users.change-role', $user['id']) }}" method="POST" class="inline" onsubmit="return confirm('¿Cambiar rol?')">
                        @csrf
                        <input type="hidden" name="role" value="{{ ($user['role'] ?? '') === 'admin' ? 'user' : 'admin' }}">
                        <button type="submit" class="bg-purple-100 text-purple-700 py-2 px-4 rounded-lg font-medium hover:bg-purple-200">
                            {{ ($user['role'] ?? '') === 'admin' ? 'Quitar admin' : 'Hacer admin' }}
                        </button>
                    </form>

                    <form action="{{ route('admin.users.destroy', $user['id']) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar permanentemente a este usuario? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-red-100 hover:text-red-700">Eliminar</button>
                    </form>
                @endif
                <a href="{{ route('admin.users.index') }}" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200">← Volver</a>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="border-b border-gray-200 overflow-x-auto">
                <nav class="flex gap-8 px-6" aria-label="Tabs">
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-primary-600 border-primary-600" data-tab="datos">Datos</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="perfil">Perfil</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="fotos">Fotos</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="posts">Posts</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="tokes">Tokes</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="matches">Matches</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="amigos">Amigos</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="solicitudes">Solicitudes</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="conversaciones">Conversaciones</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="bloques">Bloques</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="verificacion">Verificación</button>
                </nav>
            </div>
            
            <div class="p-6">
                <!-- Tab: Datos -->
                <div class="tab-content" data-tab-content="datos">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-gray-500">ID</dt>
                            <dd class="font-mono text-sm text-gray-900 break-all">{{ $user['id'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Email</dt>
                            <dd class="text-sm text-gray-900">{{ $user['email'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Rol</dt>
                            <dd class="text-sm text-gray-900">{{ ucfirst($user['role'] ?? '') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Estado</dt>
                            <dd class="text-sm text-gray-900">{{ ucfirst($user['status'] ?? '') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Verificación</dt>
                            <dd class="text-sm text-gray-900">{{ ucfirst($user['verification_status'] ?? '') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Verificado el</dt>
                            <dd class="text-sm text-gray-900">{{ $user['verified_at'] ? \Carbon\Carbon::parse($user['verified_at'])->format('d/m/Y H:i') : 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Email verificado</dt>
                            <dd class="text-sm text-gray-900">{{ $user['email_verified_at'] ? \Carbon\Carbon::parse($user['email_verified_at'])->format('d/m/Y H:i') : 'No' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Último acceso</dt>
                            <dd class="text-sm text-gray-900">{{ $user['last_seen_at'] ? \Carbon\Carbon::parse($user['last_seen_at'])->diffForHumans() : 'Nunca' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Creado</dt>
                            <dd class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($user['created_at'])->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
                
                <!-- Tab: Perfil -->
                <div class="tab-content hidden" data-tab-content="perfil">
                    @if (!empty($user['profile']))
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-gray-500">Título</dt>
                                <dd class="text-sm text-gray-900">{{ $user['profile']['title'] ?? 'No establecido' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Descripción</dt>
                                <dd class="text-sm text-gray-900">{{ $user['profile']['description'] ?? 'Sin descripción' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Fecha nacimiento</dt>
                                <dd class="text-sm text-gray-900">{{ $user['profile']['birth_date'] ? \Carbon\Carbon::parse($user['profile']['birth_date'])->format('d/m/Y') : 'No establecido' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Visibilidad perfil</dt>
                                <dd class="text-sm text-gray-900">{{ $user['profile']['profile_visibility'] ?? 'PUBLIC' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Requiere verificación</dt>
                                <dd class="text-sm text-gray-900">{{ ($user['profile']['profile_requires_verified'] ?? false) ? 'Sí' : 'No' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Descubrible</dt>
                                <dd class="text-sm text-gray-900">{{ ($user['profile']['discoverable'] ?? false) ? 'Sí' : 'No' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Precisión ubicación</dt>
                                <dd class="text-sm text-gray-900">{{ $user['profile']['location_precision_meters'] ?? 'N/A' }} metros</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Ubicación</dt>
                                <dd class="text-sm text-gray-900">
                                    @if ($user['profile']['latitude'] && $user['profile']['longitude'])
                                        {{ $user['profile']['latitude'] }}, {{ $user['profile']['longitude'] }}
                                    @else
                                        No establecida
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">GeoZona</dt>
                                <dd class="text-sm text-gray-900">{{ $user['profile']['geo_zone_id'] ?? 'Ninguna' }}</dd>
                            </div>
                        </dl>
                        
                        @if (!empty($user['profile']['fields']))
                            <h4 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Campos personalizados</h4>
                            <div class="space-y-3">
                                @foreach ($user['profile']['fields'] as $field)
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $field['field_label'] ?? $field['field_slug'] }}</p>
                                                <p class="text-sm text-gray-500">{{ $field['field_type'] }}</p>
                                            </div>
                                            <div class="text-right">
                                                <span class="badge badge-blue">{{ $field['visibility'] }}</span>
                                                @if ($field['requires_verified'])
                                                    <span class="badge badge-amber ml-1">Verificado</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-700">{{ $field['value'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <p class="text-gray-500 text-center py-8">Este usuario no tiene perfil creado.</p>
                    @endif
                </div>
                
                <!-- Tab: Fotos -->
                <div class="tab-content hidden" data-tab-content="fotos">
                    @if (!empty($user['photos']))
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            @foreach ($user['photos'] as $photo)
                                <div class="bg-gray-50 rounded-lg overflow-hidden border border-gray-200">
                                    <img src="{{ $photo['file_url'] ?? $photo['url'] }}" alt="Foto" class="w-full h-32 object-cover">
                                    <div class="p-3">
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="badge badge-blue">{{ $photo['visibility'] }}</span>
                                            @if ($photo['is_primary'])
                                                <span class="badge badge-amber">Principal</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 mb-2">{{ $photo['width'] }}x{{ $photo['height'] }} • {{ number_format($photo['size_bytes'] / 1024, 1) }} KB</p>
                                        <form action="{{ route('admin.users.delete-photo', ['photo' => $photo['id'], 'user' => $user['id']]) }}" method="POST" onsubmit="return confirm('¿Eliminar esta foto?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No hay fotos.</p>
                    @endif
                </div>
                
                <!-- Tab: Posts -->
                <div class="tab-content hidden" data-tab-content="posts">
                    @if (!empty($user['posts']))
                        <div class="space-y-4">
                            @foreach ($user['posts'] as $post)
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="badge badge-blue">{{ $post['visibility'] }}</span>
                                            <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($post['created_at'])->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500">Expira: {{ $post['expires_at'] ? \Carbon\Carbon::parse($post['expires_at'])->format('d/m/Y H:i') : 'N/A' }}</span>
                                            <form action="{{ route('admin.users.delete-post', ['post' => $post['id'], 'user' => $user['id']]) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este post?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                    <p class="text-gray-700 text-sm">{{ Str::limit($post['content_md'] ?? $post['content'] ?? '', 200) }}</p>
                                    @if ($post['attachment'])
                                        <div class="mt-2">
                                            <img src="{{ $post['attachment']['url'] }}" alt="Adjunto" class="max-h-32 rounded">
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No hay posts.</p>
                    @endif
                </div>
                
                <!-- Tab: Tokes -->
                <div class="tab-content hidden" data-tab-content="tokes">
                    @if (!empty($user['sent_tokes']) || !empty($user['received_tokes']))
                        <div class="space-y-3">
                            @if (!empty($user['sent_tokes']))
                                <h4 class="font-medium text-gray-900">Enviados</h4>
                                @foreach ($user['sent_tokes'] as $toke)
                                    <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                                        <div>
                                            <span class="badge {{ $toke['status'] === 'ACTIVE' ? 'badge-green' : 'badge-gray' }}">{{ $toke['status'] }}</span>
                                            <span class="text-sm text-gray-600 ml-2">a {{ $toke['receiver']['email'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500">Expira: {{ $toke['expires_at'] ? \Carbon\Carbon::parse($toke['expires_at'])->format('d/m/Y H:i') : 'N/A' }}</span>
                                            <form action="{{ route('admin.users.delete-toke', ['toke' => $toke['id'], 'user' => $user['id']]) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este toke?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            @if (!empty($user['received_tokes']))
                                <h4 class="font-medium text-gray-900 mt-4">Recibidos</h4>
                                @foreach ($user['received_tokes'] as $toke)
                                    <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                                        <div>
                                            <span class="badge {{ $toke['status'] === 'ACTIVE' ? 'badge-green' : 'badge-gray' }}">{{ $toke['status'] }}</span>
                                            <span class="text-sm text-gray-600 ml-2">de {{ $toke['sender']['email'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500">Expira: {{ $toke['expires_at'] ? \Carbon\Carbon::parse($toke['expires_at'])->format('d/m/Y H:i') : 'N/A' }}</span>
                                            <form action="{{ route('admin.users.delete-toke', ['toke' => $toke['id'], 'user' => $user['id']]) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este toke?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No hay tokes.</p>
                    @endif
                </div>
                
                <!-- Tab: Matches -->
                <div class="tab-content hidden" data-tab-content="matches">
                    @if (!empty($user['matches']))
                        <div class="space-y-3">
                            @foreach ($user['matches'] as $match)
                                <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                                    <div>
                                        <span class="badge badge-green">Match activo</span>
                                        <span class="text-sm text-gray-600 ml-2">con {{ $match['user_a_id'] === $user['id'] ? ($match['user_b']['email'] ?? 'N/A') : ($match['user_a']['email'] ?? 'N/A') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500">Expira: {{ $match['expires_at'] ? \Carbon\Carbon::parse($match['expires_at'])->format('d/m/Y H:i') : 'N/A' }}</span>
                                        <form action="{{ route('admin.users.delete-match', ['match' => $match['id'], 'user' => $user['id']]) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este match?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No hay matches.</p>
                    @endif
                </div>
                
                <!-- Tab: Amigos -->
                <div class="tab-content hidden" data-tab-content="amigos">
                    @if (!empty($user['friendships']))
                        <div class="space-y-3">
                            @foreach ($user['friendships'] as $friendship)
                                <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                                    <div>
                                        <span class="badge badge-green">Amigos</span>
                                        <span class="text-sm text-gray-600 ml-2">{{ $friendship['user_a_id'] === $user['id'] ? ($friendship['user_b']['email'] ?? 'N/A') : ($friendship['user_a']['email'] ?? 'N/A') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500">Desde: {{ \Carbon\Carbon::parse($friendship['created_at'])->format('d/m/Y') }}</span>
                                        <form action="{{ route('admin.users.delete-friendship', ['friendship' => $friendship['id'], 'user' => $user['id']]) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta amistad?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No hay amistades.</p>
                    @endif
                </div>
                
                <!-- Tab: Solicitudes de amistad -->
                <div class="tab-content hidden" data-tab-content="solicitudes">
                    @php
                        $sentRequests = $user['friendship_requests_sent'] ?? [];
                        $receivedRequests = $user['friendship_requests_received'] ?? [];
                    @endphp
                    @if (!empty($sentRequests) || !empty($receivedRequests))
                        @if (!empty($sentRequests))
                            <h4 class="font-medium text-gray-900 mb-3">Enviadas</h4>
                            <div class="space-y-3 mb-6">
                                @foreach ($sentRequests as $req)
                                    <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                                        <div>
                                            <span class="badge {{ $req['status'] === 'PENDING' ? 'badge-amber' : ($req['status'] === 'ACCEPTED' ? 'badge-green' : 'badge-red') }}">{{ $req['status'] }}</span>
                                            <span class="text-sm text-gray-600 ml-2">a {{ $req['addressee']['email'] ?? 'N/A' }}</span>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($req['created_at'])->format('d/m/Y') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if (!empty($receivedRequests))
                            <h4 class="font-medium text-gray-900 mb-3">Recibidas</h4>
                            <div class="space-y-3">
                                @foreach ($receivedRequests as $req)
                                    <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                                        <div>
                                            <span class="badge {{ $req['status'] === 'PENDING' ? 'badge-amber' : ($req['status'] === 'ACCEPTED' ? 'badge-green' : 'badge-red') }}">{{ $req['status'] }}</span>
                                            <span class="text-sm text-gray-600 ml-2">de {{ $req['requester']['email'] ?? 'N/A' }}</span>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($req['created_at'])->format('d/m/Y') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <p class="text-gray-500 text-center py-8">No hay solicitudes de amistad.</p>
                    @endif
                </div>
                
                <!-- Tab: Conversaciones -->
                <div class="tab-content hidden" data-tab-content="conversaciones">
                    @if (!empty($user['conversations']))
                        <div class="space-y-3">
                            @foreach ($user['conversations'] as $conv)
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <span class="badge {{ $conv['status'] === 'ACTIVE' ? 'badge-green' : 'badge-gray' }}">{{ $conv['status'] }}</span>
                                            <span class="text-sm text-gray-600 ml-2">
                                                con {{ $conv['user_a_id'] === $user['id'] ? ($conv['user_b']['email'] ?? 'N/A') : ($conv['user_a']['email'] ?? 'N/A') }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($conv['created_at'])->format('d/m/Y') }}</span>
                                            <form action="{{ route('admin.users.delete-conversation', ['conversation' => $conv['id'], 'user' => $user['id']]) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta conversación y todos sus mensajes?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                    @if ($conv['last_message'])
                                        <p class="text-xs text-gray-500 mt-1">Último mensaje: {{ Str::limit($conv['last_message']['content'] ?? '', 100) }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No hay conversaciones.</p>
                    @endif
                </div>
                
                <!-- Tab: Bloques -->
                <div class="tab-content hidden" data-tab-content="bloques">
                    @if (!empty($user['blocks']))
                        <div class="space-y-3">
                            @foreach ($user['blocks'] as $block)
                                <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                                    <div>
                                        <span class="badge badge-red">Bloqueado</span>
                                        <span class="text-sm text-gray-600 ml-2">
                                            {{ $block['blocker_id'] === $user['id'] ? 'Bloqueó a ' . ($block['blocked']['email'] ?? 'N/A') : 'Bloqueado por ' . ($block['blocker']['email'] ?? 'N/A') }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($block['created_at'])->format('d/m/Y') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No hay bloqueos.</p>
                    @endif
                </div>
                
                <!-- Tab: Verificación -->
                <div class="tab-content hidden" data-tab-content="verificacion">
                    @if (!empty($user['verification_requests']))
                        <div class="space-y-3">
                            @foreach ($user['verification_requests'] as $req)
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="badge {{ 
                                                $req['status'] === 'APPROVED' ? 'badge-green' : 
                                                ($req['status'] === 'REJECTED' ? 'badge-red' : 'badge-amber') }}">
                                                {{ $req['status'] }}
                                            </span>
                                            <span class="text-sm text-gray-600 ml-2">{{ $req['verification_method'] }}</span>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $req['submitted_at'] ? \Carbon\Carbon::parse($req['submitted_at'])->format('d/m/Y H:i') : 'N/A' }}</span>
                                    </div>
                                    @if ($req['rejection_reason'])
                                        <p class="text-sm text-red-600 mt-2">Motivo: {{ $req['rejection_reason'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No hay solicitudes de verificación.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin.layouts.app>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('[data-tab-content]');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.dataset.tab;
            
            tabBtns.forEach(b => {
                b.classList.remove('text-primary-600', 'border-primary-600');
                b.classList.add('text-gray-500', 'border-transparent');
            });
            this.classList.remove('text-gray-500', 'border-transparent');
            this.classList.add('text-primary-600', 'border-primary-600');
            
            tabContents.forEach(content => {
                if (content.dataset.tabContent === tab) {
                    content.classList.remove('hidden');
                } else {
                    content.classList.add('hidden');
                }
            });
        });
    });
});
</script>
@endpush
