@extends('layouts.admin')

@section('content')
<h4 class="mb-4">{{ $usuario ? 'Editar Usuário' : 'Novo Usuário' }}</h4>

<div class="card">
    <div class="card-body">
            <form method="POST" action="{{ $usuario ? '/admin/usuarios/editar/' . $usuario['id'] : '/admin/usuarios/novo' }}">

            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome *</label>
                    <input type="text" name="name" class="form-control" required value="{{ $usuario['name'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">E-mail *</label>
                    <input type="email" name="email" class="form-control" required value="{{ $usuario['email'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Senha {{ $usuario ? '(deixe em branco para manter)' : '*' }}</label>
                    <div class="input-group">
                        <input type="password" name="password" id="user_password" class="form-control" {{ $usuario ? '' : 'required' }} minlength="6">
                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="user_password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Perfil *</label>
                    <select name="role_id" class="form-select" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role['id'] }}" {{ ($usuario['role_id'] ?? '') == $role['id'] ? 'selected' : '' }}>
                                {{ ucfirst($role['name']) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end pb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" id="active" {{ ($usuario['active'] ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="active">Ativo</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <a href="/admin/usuarios" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
