//@ts-ignore
import classes from './Message.module.css'

export function Message(props: any) {
	return (
		<div className={props.authorId === 1 ? classes.Message : classes.myMessage}>
			<p>{props.authorId}</p>
			<p>{props.text}</p>
			<p>{props.date}</p>
		</div>
	)
}

export default Message
