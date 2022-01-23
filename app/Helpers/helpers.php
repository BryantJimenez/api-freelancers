<?php

function state($state) {
	if ($state==0) {
		return '<span class="badge badge-danger">Inactivo</span>';
	} elseif ($state==1) {
		return '<span class="badge badge-success">Activo</span>';
	} else {
		return '<span class="badge badge-dark">Desconocido</span>';
	}
}

function roleUser($user, $badge=true) {
	$num=1;
	$roles="";
	foreach ($user['roles'] as $rol) {
		if ($user->hasRole($rol->name)) {
			$roles.=($user['roles']->count()==$num) ? $rol->name : $rol->name."<br>";
			$num++;
		}
	}

	if (!is_null($user['roles']) && !empty($roles)) {
		if ($badge) {
			return '<span class="badge badge-primary">'.$roles.'</span>';
		} else {
			return $roles;
		}
	} else {
		if ($badge) {
			return '<span class="badge badge-dark">Desconocido</span>';
		} else {
			return 'Desconocido';
		}
	}
}