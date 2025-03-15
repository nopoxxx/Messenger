//@ts-ignore
import classes from './Contact.module.css'

export function Contact(props: any) {
	return (
		<li className={classes.Contact}>
			<div
				className={classes.avatar}
				style={{
					backgroundImage: `url(http://localhost/uploads/avatars/${props.avatar})`,
				}}
			/>
			<div className={classes.text}>
				<p className={classes.username}>
					{props.username !== '' ? props.username : props.email}
				</p>
				{props.email !== '' ? (
					<p className={classes.email}>
						{props.username === '' ? '' : props.email}
					</p>
				) : (
					''
				)}
			</div>
		</li>
	)
}

export default Contact
