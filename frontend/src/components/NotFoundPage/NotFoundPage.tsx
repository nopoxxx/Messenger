import { useNavigate } from 'react-router-dom'
//@ts-ignore
import classes from './NotFoundPage.module.css'

export default function NotFoundPage() {
	const navigate = useNavigate()

	const goToHomePage = () => {
		navigate('/')
	}

	return (
		<div className={classes.container}>
			<div className={classes.message}>
				<h1>404</h1>
				<p>Страница не найдена</p>
				<button className={classes.button} onClick={goToHomePage}>
					На главную
				</button>
			</div>
		</div>
	)
}
