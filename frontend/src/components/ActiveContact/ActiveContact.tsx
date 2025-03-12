//@ts-ignore
import classes from './ActiveContact.module.css'

export function ActiveContact(props: any) {
	return (
		<div className={classes.ActiveContact}>
			<div className={classes.container}>
				<p>Чат с пользователем {props.title}</p>
			</div>
		</div>
	)
}

export default ActiveContact
