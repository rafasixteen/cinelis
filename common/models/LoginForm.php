<?php

namespace common\models;

use Yii;
use yii\base\Model;

final class LoginForm extends Model
{
	public string $email = '';
	public string $password = '';
	public bool $rememberMe = true;

	public function rules(): array
	{
		return [
			[['email', 'password'], 'required'],
			['email', 'email'],
			['rememberMe', 'boolean'],
		];
	}

	public function login(): bool
	{
		if (!$this->validate()) {
			return false;
		}

		$identity = User::findByEmail($this->email);

		if ($identity === null || !$identity->validatePassword($this->password)) {
			$this->addError('password', 'Email ou palavra-passe inválidos.');

			return false;
		}

		return Yii::$app->user->login(
			$identity,
			$this->rememberMe ? 30 * 24 * 60 * 60 : 0,
		);
	}
}
