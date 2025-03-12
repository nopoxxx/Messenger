import { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { checkSession } from '../../utils/api'
import ContactList from '../ContactList/ContactList'
import LoaderPage from '../LoaderPage/LoaderPage'
import Messenger from '../Messenger/Messenger'
import SettingsList from '../SettingsList/SettingsList'
//@ts-ignore
import classes from './MessengerPage.module.css'

export function MessengerPage() {
	const [sessionCheck, setSessionCheck] = useState<boolean | null>(null)
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

	if (!sessionCheck) navigate('/auth')

	return (
		<div className={classes.MessengerPage}>
			<ContactList />
			<Messenger />
			<SettingsList />
		</div>
	)
}

export default MessengerPage
