const API_URL = 'http://localhost'

export async function register(
	email: any,
	password: any,
	username: any,
	hideEmail: any
) {
	hideEmail = hideEmail ? 0 : 1
	const response = await fetch(`${API_URL}/register`, {
		method: 'POST',
		headers: { 'Content-Type': 'application/json' },
		body: JSON.stringify({
			email,
			password,
			username,
			is_email_visible: hideEmail,
		}),
	})
	if (!response.ok) {
		throw new Error('Ошибка при авторизации')
	}

	const result = await response.json()

	document.cookie = `token=${result['desc']};`

	return result
}

export async function login(email: string, password: string) {
	const response = await fetch(`${API_URL}/login`, {
		method: 'POST',
		headers: { 'Content-Type': 'application/json' },
		body: JSON.stringify({ email, password }),
	})

	if (!response.ok) {
		throw new Error('Ошибка при авторизации')
	}

	const result = await response.json()

	document.cookie = `token=${result['desc']};`

	return result
}

export async function confirm(token: string) {
	const response = await fetch(
		`${API_URL}/confirm?token=${encodeURIComponent(token)}`,
		{
			method: 'GET',
			headers: { 'Content-Type': 'application/json' },
		}
	)

	const result = await response.json()

	if (!response.ok) {
		throw new Error(`Ошибка подтверждения: ${result.text()}`)
	}

	return result
}

export async function checkSession(): Promise<boolean | null> {
	const token = document.cookie
		.split('; ')
		.find(row => row.startsWith('token='))
		?.split('=')[1]
	const response = await fetch(`${API_URL}/verify-session`, {
		method: 'POST',
		headers: { 'Content-Type': 'application/json' },
		body: JSON.stringify({ token }),
	})
	if (!response.ok) {
		throw new Error('Ошибка при авторизации')
	}

	const result = await response.json()

	if (result['status'] === 'error') {
		document.cookie = 'token=;'
		return false
	}
	return result ?? null
}

export async function logout() {
	console.log('logout')
	const token = document.cookie
		.split('; ')
		.find(row => row.startsWith('token='))
		?.split('=')[1]
	const response = await fetch(`${API_URL}/logout`, {
		method: 'POST',
		headers: { 'Content-Type': 'application/json' },
		body: JSON.stringify({ token }),
	})
	if (!response.ok) {
		throw new Error('Ошибка при выходе')
	}

	const result = await response.json()

	if (result['status'] === 'error') {
		return false
	}

	document.cookie = 'token=;'

	return true
}
