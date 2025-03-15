import ActiveContact from '../ActiveContact/ActiveContact'
import Messages from '../Messages/Messages'
import MessengerInput from '../MessengerInput/MessengerInput'
//@ts-ignore
import classes from './Messenger.module.css'

export function Messenger(props: any) {
	return (
		<div className={classes.Messenger}>
			<ActiveContact title={props.contact} />
			<Messages contactId={props.contactId} />
			<MessengerInput contactId={props.contactId} />
		</div>
	)
}

export default Messenger
