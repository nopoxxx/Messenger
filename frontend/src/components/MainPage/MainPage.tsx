import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { checkSession } from '../../utils/api'
import LoaderPage from '../LoaderPage/LoaderPage'
//@ts-ignore
import classes from './MainPage.module.css'

export default function MainPage() {
	const [sessionCheck, setSessionCheck] = useState<boolean | null>(null)

	useEffect(() => {
		const fetchSession = async () => {
			const result = await checkSession()
			setSessionCheck(result)
		}
		fetchSession()
	}, [setSessionCheck])

	if (sessionCheck === null) {
		return <LoaderPage />
	}

	return (
		<div className={classes.MainPage}>
			<div className={classes.container}>
				<div className={classes.control}>
					<h1>Messenger </h1>
					<h2>by nopox</h2>
					{sessionCheck ? (
						<Link to={'/messenger'}>
							<button className={classes.btn}>Go to messenger</button>
						</Link>
					) : (
						<Link to={'/auth'}>
							<button className={classes.btn}>Login</button>
						</Link>
					)}
				</div>
				<img
					className={classes.image}
					src={require('../../images/MainPageImage.png')}
					alt='messenger'
				/>
			</div>
		</div>
	)
}
