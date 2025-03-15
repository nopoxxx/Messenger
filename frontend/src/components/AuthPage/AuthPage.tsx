import { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { checkSession, login, register } from '../../utils/api'
import Form from '../Form/Form'
import LoaderPage from '../LoaderPage/LoaderPage'

//@ts-ignore
import classes from './AuthPage.module.css'

export function AuthPage() {
	const [sessionCheck, setSessionCheck] = useState<boolean | null>(null)
	const [isLogin, setIsLogin] = useState(false)
	const [error, setError] = useState<string>('')
	const [isLoading, setIsLoading] = useState(false)
	const navigate = useNavigate()

	useEffect(() => {
		const fetchSession = async () => {
			const result = await checkSession()
			setSessionCheck(result)
		}
		fetchSession()
	}, [])

	if (sessionCheck === null) {
		return <LoaderPage />
	}

	if (sessionCheck) navigate('/messenger')

	const handleSubmit = async (values: Record<string, string | boolean>) => {
		setError('')
		setIsLoading(true)

		try {
			if (!isLogin) {
				const { email, password, nickname, hideEmail } = values
				const response = await register(email, password, nickname, hideEmail)
				if (response['status'] === 'ok') {
					navigate('/messenger')
				} else {
					setError(response['desc'])
				}
			} else {
				const { email, password } = values
				const response = await login(email as string, password as string)
				if (response['status'] === 'ok') {
					navigate('/messenger')
				} else {
					setError(response['desc'])
				}
			}
		} catch (error) {
			console.error('Auth error:', error)
			setError('Ошибка сети или сервера')
		} finally {
			setIsLoading(false)
		}
	}

	function switchAuthType() {
		setIsLogin(!isLogin)
	}

	return (
		<div className={classes.AuthPage}>
			{isLogin ? (
				<Form
					title='Вход'
					error={error}
					inputs={[
						{ type: 'email', title: 'Почта', name: 'email' },
						{ type: 'password', title: 'Пароль', name: 'password' },
						{
							type: 'submit',
							title: isLoading ? 'Загрузка...' : 'Войти',
							name: 'submit',
							disabled: isLoading,
						},
					]}
					onSubmit={handleSubmit}
				/>
			) : (
				<Form
					title='Регистрация'
					error={error}
					inputs={[
						{ type: 'email', title: 'Почта', name: 'email' },
						{ type: 'password', title: 'Пароль', name: 'password' },
						{ type: 'text', title: 'Никнейм', name: 'nickname' },
						{ type: 'checkbox', title: 'Скрыть почту', name: 'hideEmail' },
						{
							type: 'submit',
							title: isLoading ? 'Загрузка...' : 'Зарегистрироваться',
							name: 'submit',
							disabled: isLoading,
						},
					]}
					onSubmit={handleSubmit}
				/>
			)}

			<p className={classes.switchAuthType} onClick={switchAuthType}>
				{isLogin
					? 'Ещё нет аккаунта? Зарегистрироваться'
					: 'Уже есть аккаунт? Войти'}
			</p>
		</div>
	)
}

export default AuthPage
