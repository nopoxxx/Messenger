import { useState } from 'react'
import ActiveContact from '../ActiveContact/ActiveContact'
import Loader from '../Loader/Loader'
import Messages from '../Messages/Messages'
import MessengerInput from '../MessengerInput/MessengerInput'
//@ts-ignore
import classes from './Messenger.module.css'

export function Messenger() {
	const [isLoading, setIsLoading] = useState(false)
	return (
		<div className={classes.Messenger}>
			<ActiveContact title='Яна' />
			{isLoading ? <Loader /> : <Messages />}
			<MessengerInput />
		</div>
	)
}

export default Messenger
