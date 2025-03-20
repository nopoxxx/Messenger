//@ts-ignore
import classes from './Message.module.css'

export function Message(props: any) {
	return (
		<div
			className={
				props.sender_name === props.user_name
					? classes.myMessage
					: classes.Message
			}
			onContextMenu={props.onContextMenu}
		>
			<p>{props.sender_name}</p>
			<p className={classes.messageText}>{props.message}</p>
			<p>{props.sent_at}</p>
		</div>
	)
}

export default Message
