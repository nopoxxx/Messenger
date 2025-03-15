//@ts-ignore
import classes from './Message.module.css'

export function Message(props: any) {
	return (
		<div
			className={props.sender_id === 2 ? classes.myMessage : classes.Message}
		>
			<p>{props.sender_id}</p>
			<p className={classes.messageText}>{props.message}</p>
			<p>{props.sent_at}</p>
		</div>
	)
}

export default Message
